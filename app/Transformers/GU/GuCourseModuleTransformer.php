<?php

namespace App\Transformers\GU;

use App\Models\CourseModule;
use League\Fractal\TransformerAbstract;

class GuCourseModuleTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'lessons'
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
            'name'  => $courseModule->name,
            'description'   => $courseModule->description,
            'order_id'  => $courseModule->order_id,
            'progress'  => 0,
            'has_quiz' => false
        ];
    }

    public function includeLessons(CourseModule $courseModule)
    {
        return $this->collection($courseModule->lessons, new GuLessonTransformer($courseModule->lessons));
    }

}
