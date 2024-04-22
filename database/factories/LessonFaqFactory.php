<?php

namespace Database\Factories;

use App\Models\LessonFaq;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class LessonFaqFactory extends Factory
{
    /**
     * The name of the lessonFaq's corresponding model.
     *
     * @var string
     */
    protected $model = LessonFaq::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $lesson_id = DB::table('lessons')->pluck('id');
        return [
            'lesson_id' =>  $lesson_id->random(),
            'question'  =>  fake()->sentence,
            'answer'    =>  fake()->sentence,
        ];
    }

    public function withlessonId($id)
    {
        return $this->state(fn () => [
            'lesson_id' =>  $id,
        ]);
    }
}
