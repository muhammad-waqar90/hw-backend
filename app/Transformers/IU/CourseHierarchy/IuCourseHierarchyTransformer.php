<?php

namespace App\Transformers\IU\CourseHierarchy;

use App\Models\Course;
use League\Fractal\TransformerAbstract;

class IuCourseHierarchyTransformer extends TransformerAbstract
{

    /**
     * A Fractal transformer.
     *
     * @param Course $course
     * @return array
     */
    public function transform(Course $course)
    {
        return [
            'id'    => $course->id,
            'hierarchy_name' => $course->name,
            'name'  => $course->name,
            'type' => 'course'
        ];
    }
}
