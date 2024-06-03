<?php

namespace App\Repositories\AF;

use App\Models\PublishLesson;
use Illuminate\Support\Carbon;

class AfPublishLessonRepository
{
    private PublishLesson $publishLesson;

    public function __construct(PublishLesson $publishLesson)
    {
        $this->publishLesson = $publishLesson;
    }

    public function updateOrCreatePublishLesson($lessonId, $publishAt)
    {
        return $this->publishLesson->updateOrCreate(
            ['lesson_id' => $lessonId],
            ['publish_at' => $publishAt]
        );
    }

    public function removePublishLesson($lessonId)
    {
        return $this->publishLesson
            ->where('lesson_id', $lessonId)
            ->delete();
    }

    public function getPublishLesson($lessonId)
    {
        return $this->publishLesson
            ->where('lesson_id', $lessonId)
            ->get();
    }

    public function getReadyToPublishLessons()
    {
        return $this->publishLesson
            ->where('publish_at', '<', Carbon::now())
            ->get();
    }

    public function removePublishedLesson($lessonIds)
    {
        return $this->publishLesson
            ->whereIn('lesson_id', $lessonIds)
            ->delete();
    }
}
