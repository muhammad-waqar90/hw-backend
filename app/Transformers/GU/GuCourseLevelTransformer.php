<?php

namespace App\Transformers\GU;

use App\Models\CourseLevel;
use League\Fractal\TransformerAbstract;

class GuCourseLevelTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'course_modules'
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
            'id'    => $courseLevel->id,
            'value'    => $courseLevel->value,
            'progress'  => 0,
            'has_quiz' => false
        ];
    }

    public function includeCourseModules($courseLevel)
    {
        return $this->collection($courseLevel->courseModules, new GuCourseModuleTransformer());
    }
}
