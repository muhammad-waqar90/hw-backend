<?php

namespace App\Transformers\AF;

use App\Models\Course;
use League\Fractal\TransformerAbstract;

class AfCouponCourseRestrictionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Course $course)
    {
        return [
            'name' => $course->name,
        ];
    }
}
