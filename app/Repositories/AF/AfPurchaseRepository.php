<?php

namespace App\Repositories\AF;

use App\DataObject\Purchases\PurchaseItemStatusData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Models\Course;
use App\Models\PurchaseItem;
use App\Models\Refund;
use App\Traits\DateManipulationTrait;
use Illuminate\Support\Facades\DB;

class AfPurchaseRepository
{
    private PurchaseItem $purchaseItem;

    private Refund $refund;

    private Course $course;

    use DateManipulationTrait;

    public function __construct(PurchaseItem $purchaseItem, Refund $refund, Course $course)
    {
        $this->purchaseItem = $purchaseItem;
        $this->refund = $refund;
        $this->course = $course;
    }

    public function getUserPurchases(
        $userId,
        $searchId = null,
        $searchText = null,
        $type = null,
        $priceFrom = null,
        $priceTo = null,
        $dateFrom = null,
        $dateTo = null,
    ) {
        $items = self::getFilteredPurchaseItems($userId, $searchId, $searchText, $type, $priceFrom, $priceTo, $dateFrom, $dateTo);
        $courseIds = array_unique(array_column($items, 'course_id'));
        $itemIds = array_column($items, 'id');
        $courseItemIds = self::getLatestCoursePurchases($courseIds, $userId); //get parent course purchaseItems of selected items

        $itemIds = array_merge($itemIds, $courseItemIds);

        return $this->course
            ->select('courses.*')
            ->with('purchaseItems', function ($query) use ($itemIds, $userId) {
                $query
                    ->whereIn('purchase_items.id', $itemIds)
                    ->select('purchase_items.*', 'ea.attempts_left', 'uq.entity_type as exam_type', 'uq.score')
                    ->leftJoin('exam_accesses as ea', function ($query) use ($userId) {
                        return $query->on('ea.id', '=', 'purchase_items.entity_id')
                            ->where('ea.user_id', $userId)
                            ->where('purchase_items.entity_type', PurchaseItemTypeData::EXAM)
                            ->join('quizzes as q', function ($query) {
                                return $query->on('q.id', '=', 'ea.quiz_id')
                                    ->rightJoin('user_quizzes as uq', function ($query) {
                                        return $query->on('uq.id', '=', DB::raw('(SELECT max(id) from user_quizzes uq1 where uq1.entity_id = q.entity_id AND uq1.entity_type = q.entity_type)'));
                                    });
                            });
                    })
                    ->with('PurchaseHistory')
                    ->latest('purchase_items.updated_at');
            })
            ->whereIn('courses.id', $courseIds)
            ->paginate(15)
            ->appends([
                'searchText' => $searchText,
                'userId' => $userId,
                'type' => $type,
                'priceFrom' => $priceFrom,
                'priceTo' => $priceTo,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
    }

    public function getUserPurchaseItems($userId, $items)
    {
        return $this->purchaseItem->select('purchase_items.*')
            ->where('status', PurchaseItemStatusData::PAID)
            ->whereIn('purchase_items.id', $items)
            ->with('PurchaseHistory', function ($query) {
                $query->with('entity');
            })
            ->get();
    }

    public function getUnselectedEbooks($userId, $items)
    {
        return $this->purchaseItem
            ->select('pi.*')
            ->where('purchase_items.entity_type', PurchaseItemTypeData::COURSE)
            ->whereIn('purchase_items.id', $items)
            ->join('purchase_items as pi', function ($query) use ($items, $userId) {
                return $query->on('pi.course_id', '=', 'purchase_items.course_id')
                    ->whereNotIn('pi.id', $items)
                    ->where('pi.entity_type', PurchaseItemTypeData::EBOOK)
                    ->where('pi.status', PurchaseItemStatusData::PAID)
                    ->join('purchase_histories as ph', function ($query) use ($userId) {
                        return $query->on('ph.id', '=', 'pi.purchase_history_id')
                            ->where('ph.user_id', $userId);
                    });
            })
            ->get();
    }

    public function getFilteredPurchaseItems($userId, $searchId, $searchText, $type, $priceFrom, $priceTo, $dateFrom, $dateTo)
    {
        return $this->purchaseItem
            ->join('purchase_histories as ph', function ($query) use ($userId, $type, $searchId, $searchText, $priceFrom, $priceTo, $dateFrom, $dateTo) {
                return $query->on('ph.id', '=', 'purchase_items.purchase_history_id')
                    ->where('ph.user_id', $userId)

                    ->when($searchId, function ($query) use ($searchId) {
                        $query->where('purchase_items.id', $searchId);
                    })
                    ->when($searchText, function ($query) use ($searchText) {
                        $query->where('purchase_items.entity_name', 'LIKE', "%$searchText%");
                    })
                    ->when($type, function ($query) use ($type) {
                        $query->where('purchase_items.entity_type', $type);
                    })
                    ->when($priceFrom, function ($query) use ($priceTo, $priceFrom) {
                        $query->where('purchase_items.amount', '>=', $priceFrom)
                            ->when($priceTo, function ($query) use ($priceTo) {
                                $query->where('purchase_items.amount', '<=', $priceTo);
                            });
                    })
                    ->when($priceTo, function ($query) use ($priceTo) {
                        $query->where('purchase_items.amount', '<=', $priceTo);
                    })
                    ->when($dateFrom, function ($query) use ($dateTo, $dateFrom) {
                        $query->where('purchase_items.updated_at', '>=', $dateFrom)
                            ->when($dateTo, function ($query) use ($dateTo) {
                                $query->where('purchase_items.updated_at', '<=', $this->addDaysToDate($dateTo, 1));
                            });
                    })
                    ->when($dateTo, function ($query) use ($dateTo) {
                        $query->where('purchase_items.updated_at', '<=', $this->addDaysToDate($dateTo, 1));
                    });
            })
            ->select('purchase_items.id', 'purchase_items.course_id')
            ->get()->toArray();
    }

    public function getLatestCoursePurchases($courseIds, $userId)
    {
        return $this->purchaseItem
            ->select(DB::raw('purchase_items.course_id, max(purchase_items.id) as id'))
            ->whereIn('purchase_items.course_id', $courseIds)
            ->where('purchase_items.entity_type', PurchaseItemTypeData::COURSE)
            ->rightJoin('purchase_histories as ph', function ($query) use ($userId) {
                return $query->on('ph.id', '=', 'purchase_items.purchase_history_id')
                    ->where('ph.user_id', $userId);
            })
            ->groupBy('purchase_items.course_id')
            ->get()
            ->pluck('id')
            ->toArray();
    }

    public function saveRefundedItems($adminId, $userId, $purchaseItemId)
    {
        return $this->refund->create([
            'admin_id' => $adminId,
            'user_id' => $userId,
            'purchase_item_id' => $purchaseItemId,
        ]);
    }

    public function getRefundedItems()
    {
        return $this->refund
            ->select('refunds.*')
            ->with('purchaseItem')
            ->with('admin')
            ->with('adminProfile', function ($query) {
                $query->select('user_id', 'email');
            })
            ->latest('refunds.id')
            ->paginate(20);
    }
}
