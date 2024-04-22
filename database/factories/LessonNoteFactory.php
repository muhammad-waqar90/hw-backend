<?php

namespace Database\Factories;

use App\Models\LessonNote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class LessonNoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LessonNote::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $lessons_id = DB::table('lessons')->pluck('id');
        $users_id = DB::table('users')->pluck('id');
        return [
            'lesson_id' =>  $lessons_id->random(),
            'user_id'   =>  $users_id->random(),
            'content'   =>  '<p>' . fake()->sentence . '</p>',
        ];
    }
    public function withLessonId($id)
    {
        return $this->state(fn () => [
            'lesson_id' =>  $id,
        ]);
    }
    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id'   =>  $id,
        ]);
    }
}
