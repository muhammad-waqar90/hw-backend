<?php

namespace App\Transformers\IU\CourseHierarchy;

use App\Models\CourseLevel;
use League\Fractal\TransformerAbstract;

class IuCourseLevelHierarchyTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'parent'
    ];

    /**
     * A Fractal transformer.
     *
     * @param CourseLevel $courseLevel
     * @return array
     */
    public function transform(CourseLevel $courseLevel)
    {
        return [
            'id'             => $courseLevel->id,
            'hierarchy_name' => $courseLevel->name,
            'name'           => $courseLevel->name,
            'type'           => 'course_level'
        ];
    }

    public function includeParent($courseLevel)
    {
        return $this->item($courseLevel->course, new IuCourseHierarchyTransformer());
    }
}
