<?php

namespace App\Repositories\AF;

use App\Models\Coupon;

class AfCouponRepository
{
    private Coupon $coupon;

    public function __construct(Coupon $coupon)
    {
        $this->coupon = $coupon;
    }

    public function createCoupon(
        $name,
        $description,
        $code,
        $value,
        $valueType,
        $status,
        $redeemLimit,
        $redeemLimitPerUser,
        $individualUse
    ) {
        return $this->coupon->create([
            'name' => $name,
            'description' => $description,
            'code' => $code,
            'value' => $value,
            'value_type' => $valueType,
            'status' => $status,
            'redeem_limit' => $redeemLimit,
            'redeem_limit_per_user' => $redeemLimitPerUser,
            'individual_use' => $individualUse,
        ]);
    }

    public function getCouponList($searchText = null, $status = null, $restrictions = null)
    {
        return $this->coupon
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('code', 'LIKE', "%$searchText%");
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($restrictions, function ($query) {
                $query->with('restrictions');
            })
            ->latest('id')
            ->paginate(20)
            ->appends([
                'searchText' => $searchText,
                'status' => $status,
            ]);
    }

    public function getCoupon($id, $restrictions = false)
    {
        return $this->coupon
            ->where('id', $id)
            ->when($restrictions, function ($query) {
                return $query->with('restrictions');
            })
            ->first();
    }

    public function updateCoupon($id, $name, $description, $status, $redeemLimit, $redeemLimitPerUser)
    {
        return $this->coupon
            ->where('id', $id)
            ->update([
                'name' => $name,
                'description' => $description,
                'status' => $status,
                'redeem_limit' => $redeemLimit,
                'redeem_limit_per_user' => $redeemLimitPerUser,
            ]);
    }
}
