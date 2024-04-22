<?php

namespace App\Transformers\IU;

use App\DataObject\UserProgressData;
use App\Models\Lesson;
use App\Repositories\IU\IuCourseRepository;
use App\Traits\FileSystemsCloudTrait;
use Illuminate\Support\Facades\Lang;
use League\Fractal\TransformerAbstract;

use function PHPUnit\Framework\returnSelf;

class IuLessonTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    protected $passedPreviousLevel;
    protected $lessons;

    public function __construct($lessons, $passedPreviousLevel)
    {
        $this->lessons = $lessons;
        $this->passedPreviousLevel = $passedPreviousLevel;
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
            'name'  => $lesson->name ?: Lang::get('iu.course.lesson') . ' ' . $lesson->order_id,
            'description' => $lesson->description,
            'progress'  => $lesson->progress ?: 0,
            'order_id'  => $lesson->order_id,
            'user_note' => $lesson->userNote,
            'available' => $this->computeAvailability($lesson),
            'img' => $lesson->img ? $this->generateS3Link(IuCourseRepository::getCourseLessonThumbnailS3StoragePath().$lesson->img, 1) : null,
            'has_quiz' => !!$lesson->quiz_id,
            'failed_quiz' => !!$lesson->user_quiz_failed_id,
            'published' => $lesson->published
        ];
    }

    private function computeAvailability(Lesson $lesson)
    {
        if(!$lesson->published)
            return false;

        if(!$this->passedPreviousLevel)
            return false;

        if($lesson->order_id === 1)
            return true;

        $previousLesson = $this->lessons->firstWhere('order_id', $lesson->order_id - 1);
        if(!$previousLesson || $previousLesson->progress !== UserProgressData::COMPLETED_PROGRESS)
            return false;

        return true;
    }
}
