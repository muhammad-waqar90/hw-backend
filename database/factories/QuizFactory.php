<?php

namespace Database\Factories;

use App\DataObject\UserProgressData;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class QuizFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Quiz::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        $lessons_id = DB::table('lessons')->pluck('id');
        return [
            'entity_id'         =>  $lessons_id->random(),
            'entity_type'       =>  UserProgressData::ENTITY_LESSON,
            'duration'          =>  300,
            'num_of_questions'  =>  2
        ];
    }

    public function withDuration($duration)
    {
        return $this->state(fn () => [
            'duration'  =>  $duration,
        ]);
    }

    public function withNumOfQuestions($num)
    {
        return $this->state(fn () => [
            'num_of_questions'  =>  $num,
        ]);
    }

    public function entityLessonWithId($id)
    {
        return $this->state(fn () => [
            'entity_id'     =>  $id,
            'entity_type'   =>  UserProgressData::ENTITY_LESSON,
        ]);
    }

    public function entityCourse()
    {
        $courses_id = DB::table('courses')->pluck('id');
        return $this->state(fn () => [
            'entity_id'     =>  $courses_id->random(),
            'entity_type'   =>  UserProgressData::ENTITY_COURSE,
        ]);
    }

    public function entityCourseWithId($id)
    {
        return $this->state(fn () => [
            'entity_id'     =>  $id,
            'entity_type'   =>  UserProgressData::ENTITY_COURSE,
        ]);
    }

    public function entityCourseModule()
    {
        $course_modules_id = DB::table('course_modules')->pluck('id');
        return $this->state(fn () => [
            'entity_id'     =>  $course_modules_id->random(),
            'entity_type'   =>  UserProgressData::ENTITY_COURSE_MODULE,
        ]);
    }

    public function entityCourseModuleWithId($id)
    {
        return $this->state(fn () => [
            'entity_id'     =>  $id,
            'entity_type'   =>  UserProgressData::ENTITY_COURSE_MODULE,
        ]);
    }

    public function entityCourseLevel()
    {
        $course_levels_id = DB::table('course_levels')->pluck('id');
        return $this->state(fn () => [
            'entity_id'     =>  $course_levels_id->random(),
            'entity_type'   =>  UserProgressData::ENTITY_COURSE_LEVEL,
        ]);
    }

    public function entityCourseLevelWithId($id)
    {
        return $this->state(fn () => [
            'entity_id'     =>  $id,
            'entity_type'   =>  UserProgressData::ENTITY_COURSE_LEVEL,
        ]);
    }
}
