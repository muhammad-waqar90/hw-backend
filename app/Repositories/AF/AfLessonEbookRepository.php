<?php

namespace App\Repositories\AF;

use App\Models\Ebook;

class AfLessonEbookRepository
{
    private Ebook $ebook;

    public function __construct(Ebook $ebook)
    {
        $this->ebook = $ebook;
    }

    public function getLessonEbook($lessonId)
    {
        return $this->ebook
            ->where('lesson_id', $lessonId)
            ->first();
    }

    public function createLessonEbook($lessonId, $content)
    {
        return $this->ebook->create([
            'lesson_id' => $lessonId,
            'content' => $content,
        ]);
    }

    public function updateLessonEbook($id, $lessonId, $content)
    {
        return $this->ebook
            ->where('id', $id)
            ->where('lesson_id', $lessonId)
            ->update([
                'content' => $content,
            ]);
    }

    public function deleteLessonEbook($id)
    {
        return $this->ebook
            ->where('id', $id)
            ->delete();
    }

    public function getLessonEbookById($id)
    {
        return $this->ebook
            ->where('id', $id)
            ->first();
    }

    public function checkIfAnyLessonHasEbook($lessonIds)
    {
        $ebooks = $this->ebook
            ->whereIn('lesson_id', $lessonIds)
            ->count();

        return $ebooks > 0;
    }
}
