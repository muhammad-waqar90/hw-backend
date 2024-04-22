<?php

namespace Database\Factories;

use App\DataObject\UserProgressData;
use App\DataObject\QuizData;
use App\Models\UserQuiz;
use App\Traits\Tests\QuizQATrait;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class UserQuizFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserQuiz::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    use QuizQATrait;

    public function definition()
    {
        $lessons_id = DB::table('lessons')->pluck('id');
        $users_id = DB::table('users')->pluck('id');
        $qa = $this->QuizQuestionGenerator(1, 1, 1, 1);
        return [
            'uuid'              =>  '1',
            'user_id'           =>  $users_id->random(),
            'entity_id'         =>  $lessons_id->random(),
            'entity_type'       =>  UserProgressData::ENTITY_LESSON,
            'questions'         =>  $qa->questions,
            'answers'           =>  $qa->answers,
            'duration'          =>  300,
            'num_of_questions'  =>  2,
            'status'            =>  QuizData::STATUS_COMPLETED,
            'started_at'        =>  now(),
        ];
    }

    public function QANumber($single = null, $multiple = null, $missing = null, $linking = null)
    {
        $qas = $this->QuizQuestionGenerator($single, $multiple, $missing, $linking);
        return $this->state(fn () => [
            'questions' =>  $qas->questions,
            'answers'   =>  $qas->answers,
        ]);
    }

    public function answered($qa)
    {
        return $this->state(fn () => [
            'user_answers'  =>  $qa->answers2,
        ]);
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

    public function withScore($score)
    {
        return $this->state(fn () => [
            'score' =>  $score,
        ]);
    }

    public function inProgress()
    {
        return $this->state(fn () => [
            'status'    =>  QuizData::STATUS_IN_PROGRESS,
        ]);
    }

    public function submitted()
    {
        return $this->state(fn () => [
            'status'    =>  QuizData::STATUS_SUBMITTED,
        ]);
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id'   =>  $id,
        ]);
    }
}
