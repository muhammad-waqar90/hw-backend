<?php

namespace App\Transformers\AF;

use App\DataObject\InAppData;
use App\Models\Course;
use League\Fractal\TransformerAbstract;

class AfCourseListTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'tier',
    ];

    protected array $availableIncludes = [
        'purchaseItems',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Course $course)
    {
        return [
            'id' => $course->id,
            'product_id' => InAppData::PRODUCT_ID_PREFIX_COURSE.$course->id,
            'name' => $course->name,
            'category' => $course->category->name,
            'price' => $course->price,
            'levels' => $course->course_levels_count,
            'status' => $course->status,
            'created_at' => $course->created_at,
            'updated_at' => $course->updated_at,
        ];
    }

    public function includePurchaseItems(Course $course)
    {
        return $this->collection($course->purchaseItems, new AfPurchaseItemTransformer());
    }

    public function includeTier(Course $course)
    {
        return $this->item($course->tier, new AfTierTransformer());
    }
}
