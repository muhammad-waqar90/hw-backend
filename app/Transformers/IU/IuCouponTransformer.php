<?php

namespace App\Transformers\IU;

use App\Models\Coupon;
use League\Fractal\TransformerAbstract;

class IuCouponTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Coupon $coupon)
    {
        return [
            'name'          => $coupon->name,
            'description'   => $coupon->description,
            'code'          => $coupon->code,
            'value_type'    => $coupon->value_type,
            'value'         => $coupon->value,
        ];
    }
}
