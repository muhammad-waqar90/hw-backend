<?php

namespace App\Transformers\IU\CourseHierarchy;

use App\Models\CourseModule;
use League\Fractal\TransformerAbstract;

class IuCourseModuleHierarchyTransformer extends TransformerAbstract
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
     * @param CourseModule $courseModule
     * @return array
     */
    public function transform(CourseModule $courseModule)
    {
        return [
            'id'    => $courseModule->id,
            'order_id'  => $courseModule->order_id,
            'hierarchy_name' => $courseModule->name,
            'name'  => $courseModule->name,
            'type' => 'course_module'
        ];
    }

    public function includeParent($courseModule)
    {
        return $this->item($courseModule->courseLevel, new IuCourseLevelHierarchyTransformer());
    }
}
