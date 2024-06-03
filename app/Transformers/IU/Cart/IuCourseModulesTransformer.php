<?php

namespace App\Transformers\IU\Cart;

use App\Models\Course;
use League\Fractal\TransformerAbstract;

class IuCourseModulesTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'courseModules',
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
            'name' => $course->name,
            'img' => $course->img,
            'level_name' => $course->courseLevel->name,
        ];
    }

    public function includeCourseModules(Course $course)
    {
        return $this->collection($course->courseLevel->courseModules, new IuCartModuleExamTransformer());
    }
}
