<?php

namespace Database\Factories;

use App\DataObject\UserProgressData;
use App\Models\UserProgress;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class UserProgressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $users_id = DB::table('users')->pluck('id');
        $lessons_id = DB::table('lessons')->pluck('id');
        return [
            'user_id'       =>  $users_id->random(),
            'entity_id'     =>  $lessons_id->random(),
            'entity_type'   =>  UserProgressData::ENTITY_LESSON,
            'progress'      =>  fake()->numberBetween($min = 0, $max = 100),
        ];
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id'   =>  $id,
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

    public function withProgress($progress)
    {
        return $this->state(fn () => [
            'progress'  =>  $progress,
        ]);
    }
}
