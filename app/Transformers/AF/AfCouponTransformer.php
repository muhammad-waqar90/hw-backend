<?php

namespace App\Transformers\AF;

use App\Models\Coupon;
use League\Fractal\TransformerAbstract;

class AfCouponTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'restrictions',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Coupon $coupon)
    {
        return [
            'id' => $coupon->id,
            'name' => $coupon->name,
            'description' => $coupon->description,
            'code' => $coupon->code,
            'value' => $coupon->value,
            'value_type' => $coupon->value_type,
            'status' => $coupon->status,
            'redeem_count' => $coupon->redeem_count,
            'redeem_limit' => $coupon->redeem_limit,
            'redeem_limit_per_user' => $coupon->redeem_limit_per_user,
            'individual_use' => $coupon->individual_use,
            'created_at' => $coupon->created_at,
            'updated_at' => $coupon->updated_at,
        ];
    }

    public function includeRestrictions($coupon)
    {
        return $this->collection($coupon->restrictions, new AfCouponRestrictionTransformer());
    }
}
