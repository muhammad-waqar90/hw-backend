<?php

namespace App\Transformers\IU\Quiz;

use App\Models\UserQuiz;
use League\Fractal\TransformerAbstract;

class IuUserQuizTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(UserQuiz $userQuiz)
    {
        return [
            'id' => $userQuiz->id,
            'questions' => $userQuiz->questions,
            'duration' => $userQuiz->duration,
            'started_at' => $userQuiz->started_at,
            'num_of_questions' => $userQuiz->num_of_questions,
            'uuid' => $userQuiz->uuid,
        ];
    }
}
