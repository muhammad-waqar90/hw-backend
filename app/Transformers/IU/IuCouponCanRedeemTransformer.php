<?php

namespace App\Transformers\IU;

use App\DataObject\CouponData;
use App\Models\CouponRestriction;
use League\Fractal\TransformerAbstract;

class IuCouponCanRedeemTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(CouponRestriction $couponRestriction)
    {
        return [
            'item_id'   => $couponRestriction->entity_id,
            'item_type' => CouponData::MODEL_ENTITY[$couponRestriction->entity_type],
        ];
    }
}
