<?php

namespace App\Repositories\AF;

use App\Models\LessonFaq;

class AfLessonFaqRepository
{
    private LessonFaq $lessonFaq;

    public function __construct(LessonFaq $lessonFaq)
    {
        $this->lessonFaq = $lessonFaq;
    }

    public function getLessonFaq($id)
    {
        return $this->lessonFaq
            ->where('id', $id)
            ->get();
    }

    public function getLessonFaqByLessonId($lessonId, $questionText = '')
    {
        return $this->lessonFaq
            ->where('lesson_id', $lessonId)
            ->when($questionText, function ($query) use ($questionText) {
                $query->where('question', 'LIKE', "$questionText");
            })
            ->first();
    }

    public function getLessonFaqList($lessonId, $searchText = '')
    {
        return $this->lessonFaq
            ->where('lesson_id', $lessonId)
            ->when($searchText, function ($query) use ($searchText) {
                return $query->where(function ($query) use ($searchText) {
                    $query->where('question', 'LIKE', "%$searchText%")
                        ->orWhere('answer', 'LIKE', "%$searchText%");
                });
            })
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function createLessonFaq($lessonId, $question, $answer)
    {
        return $this->lessonFaq->create([
            'lesson_id' =>  $lessonId,
            'question'  =>  $question,
            'answer'    =>  $answer
        ]);
    }

    public function updateLessonFaq($id, $lessonId, $question, $answer)
    {
        return $this->lessonFaq
            ->where('id', $id)
            ->where('lesson_id', $lessonId)
            ->update([
                'question'  =>  $question,
                'answer'    =>  $answer
            ]);
    }

    public function deleteLessonFaq($id)
    {
        return $this->lessonFaq
            ->where('id', $id)
            ->delete();
    }
}
