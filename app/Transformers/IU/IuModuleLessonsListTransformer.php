<?php

namespace App\Transformers\IU;

use App\DataObject\UserProgressData;
use App\Models\Lesson;
use Illuminate\Support\Facades\Lang;
use League\Fractal\TransformerAbstract;

class IuModuleLessonsListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Lesson $lesson
     * @return array
     */

    protected $lessons;

    public function __construct($lessons)
    {
        $this->lessons = $lessons;
    }

    public function transform(Lesson $lesson)
    {
        return [
            'id' => $lesson->id,
            'name' => $lesson->name ? $lesson->name : Lang::get('iu.course.lesson') . ' ' . $lesson->order_id,
            'progress' => $lesson->progress,
            'course_id' => $lesson->course_id,
            'course_module_id' => $lesson->course_module_id,
            'failed_quiz' => !!$lesson->user_quiz_failed_id,
            'available' => $this->computeAvailability($lesson),
            'has_quiz' => !!$lesson->quiz_id,
            'order_id' => $lesson->order_id,
            'published' => $lesson->published
        ];
    }

    private function computeAvailability(Lesson $lesson)
    {
        if($lesson->order_id === 1)
            return true;

        $previousLesson = $this->lessons->firstWhere('order_id', $lesson->order_id - 1);
        if(!$previousLesson || $previousLesson->progress !== UserProgressData::COMPLETED_PROGRESS)
            return false;

        return true;
    }
}
