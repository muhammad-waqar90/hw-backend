<?php

namespace App\Transformers\GU\Cart;

use App\Models\Course;
use League\Fractal\TransformerAbstract;

class GuCartCourseEbooksTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'courseModules'
    ];

    /**
     * A Fractal transformer.
     *
     * @param Course $course
     * @return array
     */
    public function transform(Course $course)
    {
        return [
            'id' => $course->id,
            'name' => $course->name,
            'img' => $course->img,
            'level_value' => $course->courseLevel->value
        ];
    }

    public function includeCourseModules(Course $course)
    {
        return $this->collection($course->courseLevel->courseModules, new GuCartEbookModulesTransformer());
    }
}
