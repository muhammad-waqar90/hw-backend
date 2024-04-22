<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LessonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lesson::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $courses_id = DB::table('courses')->pluck('id');
        $course_modules_id = DB::table('course_modules')->pluck('id');
        return [
            'course_id'         =>  $courses_id->random(),
            'course_module_id'  =>  $course_modules_id->random(),
            'order_id'          =>  fake()->unique->randomDigitNotNull,
            'name'              =>  Str::random(10),
            'img'               =>  fake()->imageUrl,
            'description'       =>  fake()->sentence,
            'content'           =>  fake()->text,
            'video'             =>  fake()->url,
        ];
    }
    public function withCourseId($id)
    {
        return $this->state(fn () => [
            'course_id' =>  $id,
        ]);
    }
    public function withCourseModuleId($id)
    {
        return $this->state(fn () => [
            'course_module_id'  =>  $id,
        ]);
    }
    public function withOrder($order)
    {
        return $this->state(fn () => [
            'order_id'  =>  $order,
        ]);
    }
}
