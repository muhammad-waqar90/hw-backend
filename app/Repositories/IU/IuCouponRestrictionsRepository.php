<?php

namespace App\Repositories\IU;

use App\Models\CouponRestriction;

class IuCouponRestrictionsRepository
{
    private CouponRestriction $couponRestriction;

    public function __construct(CouponRestriction $couponRestriction)
    {
        $this->couponRestriction = $couponRestriction;
    }

    public function getCouponRestrictions($couponId, $entities, $entityType)
    {
        return $this->couponRestriction
            ->where('coupon_id', $couponId)
            ->whereIn('entity_id', $entities->map(function ($item) {
                return $item['id'];
            }))
            ->where('entity_type', $entityType)
            ->get();
    }
}
