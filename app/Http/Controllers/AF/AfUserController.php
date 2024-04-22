<?php

namespace App\Http\Controllers\AF;

use App\DataObject\RoleData;
use App\DataObject\Tickets\TicketCategoryData;
use App\Events\Tickets\IuAccountTrashed;
use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Users\AfUserCourseListRequest;
use App\Http\Requests\AF\Users\AfUserListRequest;
use App\Http\Requests\AF\Users\AfUserPurchaseListRequest;
use App\Jobs\ExportUserGdprDataJob;
use App\Mail\IU\Account\IuAccountDeletedEmail;
use App\Mail\IU\Account\IuAccountTrashedEmail;
use App\Repositories\AF\AfCourseRepository;
use App\Repositories\AF\AfPurchaseRepository;
use App\Repositories\GdprRepository;
use App\Repositories\IU\IuUserRepository;
use App\Repositories\AF\AfUserRepository;
use App\Repositories\TicketRepository;
use App\Transformers\AF\AfCourseListTransformer;
use App\Transformers\AF\AfPurchaseItemTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AfUserController extends Controller
{
    private AfUserRepository $afUserRepository;
    private IuUserRepository $iuUserRepository;
    private AfCourseRepository $afCourseRepository;
    private AfPurchaseRepository $afPurchaseRepository;
    private GdprRepository $gdprRepository;
    private TicketRepository $ticketRepository;

    public function __construct(
        AfUserRepository $afUserRepository,
        IuUserRepository $iuUserRepository,
        AfCourseRepository $afCourseRepository,
        AfPurchaseRepository $afPurchaseRepository,
        GdprRepository $gdprRepository,
        TicketRepository $ticketRepository
    ) {
        $this->afUserRepository = $afUserRepository;
        $this->iuUserRepository = $iuUserRepository;
        $this->afCourseRepository = $afCourseRepository;
        $this->afPurchaseRepository = $afPurchaseRepository;
        $this->gdprRepository = $gdprRepository;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Query filter params
     * @param AfUserListRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersList(AfUserListRequest $request)
    {
        $data = $this->afUserRepository->getUserList(
            RoleData::INDEPENDENT_USER,
            $request->query('searchText'),
            $request->query('activeStatus'),
            $request->query('courseId'),
        );
        $data->makeVisible('deleted_at');

        return response()->json($data, 200);
    }

    public function getUser($id)
    {
        $user = $this->iuUserRepository->getUser($id, true, RoleData::INDEPENDENT_USER, true);
        if(!$user)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $user->makeVisible('deleted_at');
        return response()->json($user, 200);
    }

    public function getUserCourses($id, AfUserCourseListRequest $request)
    {
        $user = $this->iuUserRepository->getUser($id, true, RoleData::INDEPENDENT_USER, true);
        if(!$user)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $data = $this->afCourseRepository->getUserEnrolledCourses($id, (string) $request->query('searchText'));
        return response()->json($data, 200);
    }

    public function enableUser($id)
    {
        $user = $this->iuUserRepository->getUser($id);

        if(!$user)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        if($user->is_enabled)
            return response()->json(['errors' => Lang::get('auth.alreadyEnabled')], 400);

        $this->iuUserRepository->enableUser($user);

        return response()->json(['message' => Lang::get('auth.enableUser')], 200);
    }

    public function disableUser($id)
    {
        $user = $this->iuUserRepository->getUser($id);

        if(!$user)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        if(!$user->is_enabled)
            return response()->json(['errors' => Lang::get('auth.alreadyDisable')], 400);

        $this->iuUserRepository->disableUser($user);

        return response()->json(['message' => Lang::get('auth.disableUser')], 200);
    }

    public function getUserPurchases($id, AfUserPurchaseListRequest $request)
    {
        $user = $this->iuUserRepository->getUser($id, true, RoleData::INDEPENDENT_USER, true);

        if(!$user)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $data = $this->afPurchaseRepository->getUserPurchases(
            $id,
            (int) $request->query('searchId'),
            (string) $request->query('searchText'),
            (string) $request->query('type'),
            (int) $request->query('priceFrom'),
            (int) $request->query('priceTo'),
            $request->query('dateFrom'),
            $request->query('dateTo')
        );
        $fractal = fractal($data->getCollection(), new AfCourseListTransformer)
            ->parseIncludes('purchaseItems');
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getUnselectedEbooks($id, $items)
    {
        $user = $this->iuUserRepository->getUser($id, false, RoleData::INDEPENDENT_USER, true);

        if(!$user)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $items = array_map('intval', explode(',', $items));

        $data = $this->afPurchaseRepository->getUnselectedEbooks($id, $items);
        $data = fractal($data, new AfPurchaseItemTransformer());

        return response()->json($data, 200);
    }

    public function deleteUser(int $id)
    {
        DB::beginTransaction();
        try {
            $user = $this->iuUserRepository->getUser($id, true, RoleData::INDEPENDENT_USER, true);
            if(!$user)
                return response()->json(['errors' => Lang::get('general.notFound')], 404);

            if(!$user->restoreUser) {
                // soft delete: create restore user
                $exists = $this->ticketRepository->checkIfAnyUnResolvedTicketsExist($user->id, [TicketCategoryData::REFUND]);
                if($exists)
                    return response()->json(['errors' => Lang::get('auth.unresolvedRefundRequests')], 400);

                $this->iuUserRepository->createRestoreUser($user->id);

                IuAccountTrashed::dispatch($user->id);

                Mail::to($user->userProfile->email)->queue(new IuAccountTrashedEmail($user));

                $resMessage = 'Account has marked for deletion';
            } else {
                // hard delete: remove all relevant data
                Mail::to($user->userProfile->email)->queue(new IuAccountDeletedEmail());
                $user->forceDelete();
                $resMessage = 'Account successfully deleted';
            }

            DB::commit();
            return response()->json(['message' => $resMessage], 200);
        } catch(\Exception $e) {
            DB::rollback();

            Log::error('Exception: AfUserController@deleteUser', [$e->getMessage()]);
            if($e->getCode() == 23000)
                return response()->json(['errors' => $e->getMessage()], 400);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function exportUserGDPRData(int $id)
    {
        $user = $this->iuUserRepository->getUser($id);
        if(!$user)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $gdprRequest = $this->gdprRepository->init($id);
        ExportUserGdprDataJob::dispatch($user->id, $gdprRequest->uuid)->onQueue('low');

        return response()->json(['message' => Lang::get('iu.gdprRequest.successfullyInit')], 201);
    }

}
