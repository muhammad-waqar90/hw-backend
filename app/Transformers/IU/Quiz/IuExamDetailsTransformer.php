<?php

namespace App\Transformers\IU\Quiz;

use App\Models\Quiz;
use League\Fractal\TransformerAbstract;

class IuExamDetailsTransformer extends TransformerAbstract
{
    private $userCanAccessExam;

    private $userExamAttemptsLeft;

    public function __construct($userCanAccessExam, $userExamAttemptsLeft)
    {
        $this->userCanAccessExam = $userCanAccessExam;
        $this->userExamAttemptsLeft = $userExamAttemptsLeft;
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Quiz $quiz)
    {
        return [
            'price' => $quiz->price,
            'user_can_access_exam' => $this->userCanAccessExam,
            'user_exam_attempts_left' => $this->userExamAttemptsLeft,
        ];
    }
}
