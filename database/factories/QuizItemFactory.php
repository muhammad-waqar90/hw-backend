<?php

namespace Database\Factories;

use App\DataObject\QuizData;
use App\DataObject\QuizQuestionDifficultyData;
use App\Models\QuizItem;
use App\Traits\Tests\QuizQATrait;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class QuizItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    use QuizQATrait;

    public function definition()
    {
        $quiz_id = DB::table('quizzes')->pluck('id');
        $qa = $this->QuizItemEntryGenerator();

        return [
            'quiz_id' => $quiz_id->random(),
            'uuid' => '9777d56c-9dfd-465d-801a-dd7169f5a145',
            'type' => QuizData::QUESTION_MCQ_SINGLE,
            'difficulty' => QuizQuestionDifficultyData::DIFFICULT_INT,
            'question' => $qa->question,
            'options' => $qa->options,
            'answer' => $qa->answer,
        ];
    }

    public function withQuizId($id)
    {
        return $this->state(fn () => [
            'quiz_id' => $id,
        ]);
    }
}
