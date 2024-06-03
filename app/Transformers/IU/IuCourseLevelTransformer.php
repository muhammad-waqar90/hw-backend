<?php

namespace App\Transformers\IU;

use App\Models\CourseLevel;
use League\Fractal\TransformerAbstract;

class IuCourseLevelTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'course_modules',
    ];

    protected $passedPreviousLevel;

    public function __construct($passedPreviousLevel)
    {
        $this->passedPreviousLevel = $passedPreviousLevel;
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(CourseLevel $courseLevel)
    {
        return [
            'id' => $courseLevel->id,
            'value' => $courseLevel->value,
            'name' => $courseLevel->name,
            'progress' => $courseLevel->progress ?: 0,
            'has_quiz' => $courseLevel->quiz_id ? true : false,
            'failed_quiz' => (bool) $courseLevel->user_quiz_failed_id,
        ];
    }

    public function includeCourseModules($courseLevel)
    {
        return $this->collection($courseLevel->courseModules, new IuCourseModuleTransformer($this->passedPreviousLevel));
    }
}
