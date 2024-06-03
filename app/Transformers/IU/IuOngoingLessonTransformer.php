<?php

namespace App\Transformers\IU;

use App\Models\Lesson;
use League\Fractal\TransformerAbstract;

class IuOngoingLessonTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Lesson $lesson)
    {
        return [
            'id' => $lesson->id,
            'lesson_name' => $lesson->lesson_name,
            'module_name' => $lesson->module_name,
            'level_name' => $lesson->level_name,
            'published' => $lesson->published,
        ];
    }
}
