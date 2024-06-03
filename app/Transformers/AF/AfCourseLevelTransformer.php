<?php

namespace App\Transformers\AF;

use App\Models\CourseLevel;
use League\Fractal\TransformerAbstract;

class AfCourseLevelTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'course_modules',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(CourseLevel $courseLevel)
    {
        return [
            'id' => $courseLevel->id,
            'course_id' => $courseLevel->course_id,
            'value' => $courseLevel->value,
            'name' => $courseLevel->name,
            'created_at' => $courseLevel->created_at,
            'updated_at' => $courseLevel->updated_at,
        ];
    }

    public function includeCourseModules($courseLevel)
    {
        return $this->collection($courseLevel->courseModules, new AfCourseModuleTransformer());
    }
}
