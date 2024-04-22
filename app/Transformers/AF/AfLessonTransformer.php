<?php

namespace App\Transformers\AF;

use App\Models\Lesson;
use App\Repositories\AF\AfLessonRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class AfLessonTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'quiz'
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Lesson $lesson)
    {
        return [
            'id'                => $lesson->id,
            'course_id'         => $lesson->course_id,
            'course_module_id'  => $lesson->course_module_id,
            'order_id'          => $lesson->order_id,
            'name'              => $lesson->name,
            'img'               => $lesson->img ? $this->generateS3Link(AfLessonRepository::getThumbnailS3StoragePath() . $lesson->img, 1) : null,
            'description'       => $lesson->description,
            'content'           => $lesson->content,
            'video'             => $lesson->video,
            'published'         => $lesson->published,
            'publish_at'        => !$lesson->published ? $lesson->publishLesson->publish_at : null,
            'created_at'        => $lesson->created_at,
            'updated_at'        => $lesson->updated_at,
        ];
    }

    public function includeQuiz(Lesson $lesson)
    {
        return $lesson->quiz
            ? $this->item($lesson->quiz, new AfQuizTransformer())
            : null;
    }
}
