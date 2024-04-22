<?php

namespace App\Http\Controllers\AF;

use App\DataObject\Notifications\NotificationTypeData;
use App\DataObject\Purchases\PurchaseItemStatusData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Refunds\AfRefundRequest;
use App\Mail\IU\Refund\IuPurchasesRefundedEmail;
use App\Repositories\AF\AfCourseRepository;
use App\Repositories\AF\AfNotificationRepository;
use App\Repositories\AF\AfPurchaseRepository;
use App\Repositories\IU\IuEbookRepository;
use App\Repositories\IU\IuPurchaseRepository;
use App\Repositories\IU\IuQuizRepository;
use App\Repositories\IU\IuUserRepository;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AfPurchaseController extends Controller
{
    private IuUserRepository $iuUserRepository;
    private AfNotificationRepository $afNotificationRepository;
    private AfPurchaseRepository $afPurchaseRepository;
    private IuPurchaseRepository $iuPurchaseRepository;
    private AfCourseRepository $afCourseRepository;
    private IuEbookRepository $iuEbookRepository;
    private IuQuizRepository $iuQuizRepository;

    public function __construct(
        IuUserRepository $iuUserRepository,
        AfNotificationRepository $afNotificationRepository,
        AfPurchaseRepository $afPurchaseRepository,
        IuPurchaseRepository $iuPurchaseRepository,
        AfCourseRepository $afCourseRepository,
        IuEbookRepository $iuEbookRepository,
        IuQuizRepository $iuQuizRepository
    ) {
        $this->iuUserRepository = $iuUserRepository;
        $this->afNotificationRepository = $afNotificationRepository;
        $this->afPurchaseRepository = $afPurchaseRepository;
        $this->iuPurchaseRepository = $iuPurchaseRepository;
        $this->afCourseRepository = $afCourseRepository;
        $this->iuEbookRepository = $iuEbookRepository;
        $this->iuQuizRepository = $iuQuizRepository;
    }

    public function refund($id, AfRefundRequest $request)
    {
        $adminId = $request->user()->id;
        $user = $this->iuUserRepository->getUser($id, true);
        $customer = $user->customer;
        if (!$customer)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $items = array_column($request->toArray(), 'id');
        $validPurchaseItems = $this->afPurchaseRepository->getUserPurchaseItems($id, $items);
        $unselectedEbooks = $this->afPurchaseRepository->getUnselectedEbooks($id, $items);
        if ($validPurchaseItems->isEmpty() || !$unselectedEbooks->isEmpty() || count($items) !== count($validPurchaseItems))
            return response()->json(['errors' => Lang::get('general.invalidData')], 400);

        $purchaseHistoryItems = $validPurchaseItems->groupBy('purchase_history_id');
        $refunds = $purchaseHistoryItems->map(function ($item) {
            return $item->sum('amount');
        });

        try {
            foreach ($refunds as $historyId => $amount) {
                // payment_intent ($historyId) = null, $amount === 0.0 > Free Item
                if ($historyId && $amount !== 0.0) {
                    $stripeId = $purchaseHistoryItems[$historyId][0]['PurchaseHistory']['entity']['stripe_id'];
                    $this->handleStripeRefund($customer, $stripeId, $amount);
                }

                $this->revokeAccess($purchaseHistoryItems[$historyId], $customer->user_id);
                $this->saveRefundedItems($adminId, $customer->user_id, $purchaseHistoryItems[$historyId]);
            }

            $this->deleteQasNotifications($validPurchaseItems, $customer->user_id);
            Mail::to($user->userProfile->email)->queue(new IuPurchasesRefundedEmail($user, $validPurchaseItems));

            return response()->json(['message' => 'Successfully refunded'], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfPurchaseController@refund', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    private function handleStripeRefund($customer, $pi, $amount)
    {
        return $customer->refund(
            $pi,
            ['amount' => $amount * 100]
        );
    }

    public function revokeAccess($items, $userId)
    {
        foreach ($items as $item) {
            $this->iuPurchaseRepository->updatePurchaseItemStatus($item->id, PurchaseItemStatusData::REFUNDED);
            if ($item->entity_type === PurchaseItemTypeData::COURSE)
                $this->afCourseRepository->revokeCourseAccessFromUser($userId, $item->entity_id);
            if ($item->entity_type === PurchaseItemTypeData::EBOOK)
                $this->iuEbookRepository->revokeEbookAccessFromUser($userId, $item->entity_id);
            if (
                $item->entity_type === PurchaseItemTypeData::EXAM
            ) {
                $this->iuQuizRepository->revokeExamAccessFromUser($userId, $item->entity_id);
                $this->iuQuizRepository->invalidateUserRefundedExam($userId, $item->entity_id);
            }
        }
    }

    public function saveRefundedItems($adminId, $userId, $items)
    {
        foreach ($items as $item) {
            $this->afPurchaseRepository->saveRefundedItems($adminId, $userId, $item->id);
        }
    }

    private function deleteQasNotifications($items, $userId)
    {
        $itemsArray = $items->toArray();
        $coursePurchaseItems = array_filter($itemsArray, function ($item) {
            return $item['entity_type'] === PurchaseItemTypeData::COURSE;
        });
        $courseIds = array_column($coursePurchaseItems, 'entity_id');
        $this->afNotificationRepository->deleteNotifications($userId, $courseIds, NotificationTypeData::LESSON_QA_TICKET);
    }

    public function getRefundedItems()
    {
        $data = $this->afPurchaseRepository->getRefundedItems();
        return response()->json($data, 200);
    }
}
