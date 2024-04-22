<?php

namespace App\Transformers\AF;

use App\DataObject\CouponData;
use App\Models\CouponRestriction;
use League\Fractal\TransformerAbstract;

class AfCouponRestrictionTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'entity'
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(CouponRestriction $couponRestriction)
    {
        return [
            'id'            => $couponRestriction->id,
            'entity_id'     => $couponRestriction->entity_id,
            'entity_type'   => CouponData::MODEL_ENTITY[$couponRestriction->entity_type],
        ];
    }

    public function includeEntity($couponRestriction)
    {
        if($couponRestriction->entity_type == CouponData::ENTITY_MODEL['course'])
	        return $this->item($couponRestriction->entity, new AfCouponCourseRestrictionTransformer());
        // TODO: requird for other entity types as well
    }
}
