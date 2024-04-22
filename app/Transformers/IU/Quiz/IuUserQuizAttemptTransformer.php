<?php

namespace App\Transformers\IU\Quiz;

use App\DataObject\QuizData;
use App\Models\UserQuiz;
use League\Fractal\TransformerAbstract;

class IuUserQuizAttemptTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param UserQuiz $userQuiz
     * @return array
     */
    public function transform(UserQuiz $userQuiz)
    {
        return [
            'id'    => $userQuiz->id,
            'questions'  => $userQuiz->questions,
            'duration'  => $userQuiz->duration,
            'started_at'  => $userQuiz->started_at,
            'num_of_questions' => $userQuiz->num_of_questions,
            'user_answers' => $userQuiz->user_answers,
            'score' => $userQuiz->score,
            'updated_at' => $userQuiz->updated_at,
            'has_passed' => $userQuiz->score >= QuizData::DEFAULT_PASSING_SCORE
        ];
    }
}
