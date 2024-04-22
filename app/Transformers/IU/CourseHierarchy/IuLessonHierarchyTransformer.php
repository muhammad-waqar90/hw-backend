<?php

namespace App\Transformers\IU\CourseHierarchy;

use App\Models\Lesson;
use Illuminate\Support\Facades\Lang;
use League\Fractal\TransformerAbstract;

class IuLessonHierarchyTransformer extends TransformerAbstract
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
     * @param Lesson $lesson
     * @return array
     */
    public function transform(Lesson $lesson)
    {
        return [
            'id'    => $lesson->id,
            'order_id'  => $lesson->order_id,
            'hierarchy_name' => $lesson->name ? $lesson->name : Lang::get('iu.course.lesson') . ' ' . $lesson->order_id,
            'name'  => $lesson->name,
            'type' => 'lesson'
        ];
    }

    public function includeParent($lesson)
    {
        return $this->item($lesson->courseModule, new IuCourseModuleHierarchyTransformer());
    }
}
