<?php

namespace App\Transformers\GU;

use App\DataObject\UserProgressData;
use App\Models\Lesson;
use Illuminate\Support\Facades\Lang;
use League\Fractal\TransformerAbstract;

class GuLessonTransformer extends TransformerAbstract
{
    protected $passedPreviousLevel;
    protected $lessons;

    public function __construct($lessons)
    {
        $this->lessons = $lessons;
    }

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
            'name'  => $lesson->name ? $lesson->name : Lang::get('iu.course.lesson') . ' ' . $lesson->order_id,
            'description' => $lesson->description,
            'progress'  => 0,
            'order_id'  => $lesson->order_id,
            'user_note' => $lesson->userNote,
            'available' => false,
            'img'   => $lesson->img ? $lesson->img : null
        ];
    }
}
