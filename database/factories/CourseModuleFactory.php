<?php

namespace Database\Factories;

use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseModuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseModule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $courses_id = DB::table('courses')->pluck('id');
        $course_levels_id = DB::table('course_levels')->pluck('id');
        return [
            'course_id'         =>  $courses_id->random(),
            'course_level_id'   =>  $course_levels_id->random(),
            'order_id'          =>  fake()->randomDigit,
            'name'              =>  Str::random(10),
            'description'       =>  fake()->sentence,
            'img'               =>  fake()->imageUrl(),
        ];
    }
    public function withCourseId($id)
    {
        return $this->state(fn () => [
            'course_id' =>  $id,
        ]);
    }
    public function withCourseLevelId($id)
    {
        return $this->state(fn () => [
            'course_level_id'   =>  $id,
        ]);
    }
    public function withOrderId($order)
    {
        return $this->state(fn () => [
            'order_id'  =>  $order,
        ]);
    }
    /*
    public function withMultipleOrder($orderNum)
    {
        return $this->state(fn () => [
            'order_id'   =>  fake()->unique()->numberBetween($min = 1, $orderNum = 3),
        ]);
    }
    */
}
