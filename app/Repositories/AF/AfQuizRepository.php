<?php

namespace App\Repositories\AF;

use App\Models\Quiz;

class AfQuizRepository
{
    private Quiz $quiz;

    public function __construct(Quiz $quiz)
    {
        $this->quiz = $quiz;
    }

    public function updateOrCreateDummyQuiz($entityId, $entityType)
    {
        return $this->quiz
            ->updateOrCreate([
                'entity_id' => $entityId,
                'entity_type' => $entityType,
            ], [
                'duration' => 0,
                'num_of_questions' => 0,
            ]);
    }

    public function deleteDummyQuiz($entityId, $entityType)
    {
        return $this->quiz
            ->where([
                ['entity_id', $entityId],
                ['entity_type', $entityType],
            ])->delete();
    }
}
