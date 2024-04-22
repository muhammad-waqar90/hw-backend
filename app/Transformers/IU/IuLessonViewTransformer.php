<?php

namespace App\Transformers\IU;

use App\Models\Lesson;
use App\Repositories\IU\IuCourseRepository;
use App\Traits\FileSystemsCloudTrait;
use Illuminate\Support\Facades\Lang;
use League\Fractal\TransformerAbstract;

class IuLessonViewTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * A Fractal transformer.
     *
     * @param Lesson $lesson
     * @return array
     */
    public function transform(Lesson $lesson)
    {
        return [
            'has_ebook' => $lesson->has_ebook,
            'has_purchased_ebook' => $lesson->has_purchased_ebook,
            'hide_ebook_prompt' => !$lesson->has_ebook || $lesson->has_purchased_ebook || $lesson->disable_ebook_prompt,
            'course_id'=> $lesson->course_id,
            'course_module_id'=> $lesson->course_module_id,
            'course_module_name'=> $lesson->course_module_name,
            'created_at'=> $lesson->created_at,
            'description'=> $lesson->description,
            'id'=> $lesson->id,
            'img' => $lesson->img ? $this->generateS3Link(IuCourseRepository::getCourseLessonThumbnailS3StoragePath().$lesson->img, 1) : null,
            'level'=> $lesson->level,
            'name'=> $lesson->name ?: Lang::get('iu.course.lesson') . ' ' . $lesson->order_id,
            'notes_text'=> $lesson->notes_text,
            'notes_updated_at' => $lesson->notes_updated_at,
            'order_id'=> $lesson->order_id,
            'progress'=> $lesson->progress,
            'updated_at'=> $lesson->updated_at,
            'video'=> $lesson->video,
            'video_progress'=> $lesson->video_progress,
            'has_quiz' => $lesson->quiz_id ? true : false,
            'lesson_faqs' => $lesson->lessonFaqs ?: null,
            'has_module_exam' => !!$lesson->has_module_exam,
            'module_progress' => $lesson->module_progress ?: 0,
            'published' => $lesson->published
        ];
    }
}
