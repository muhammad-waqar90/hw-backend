<?php

namespace Database\Factories;

use App\Models\CourseLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class CourseLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $courses_id = DB::table('courses')->pluck('id');

        return [
            'course_id' => $courses_id->random(),
            'value' => fake()->randomDigit,
        ];
    }

    public function withCourseId($id)
    {
        return $this->state(fn () => [
            'course_id' => $id,
        ]);
    }

    public function withValue($value)
    {
        return $this->state(fn () => [
            'value' => $value,
        ]);
    }
}
