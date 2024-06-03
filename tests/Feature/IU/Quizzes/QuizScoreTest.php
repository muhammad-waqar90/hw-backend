<?php

namespace Tests\Feature\IU\Quizzes;

use App\Models\User;
use App\Models\UserQuiz;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\FinishedLessonTrait;
use App\Traits\Tests\JSONResponseTestTrait;
use App\Traits\Tests\QuizQATrait;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuizScoreTest extends TestCase
{
    use CourseTestTrait;
    use FinishedLessonTrait;
    use JSONResponseTestTrait;
    use QuizQATrait;

    private $user;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);

        $this->finishLessonReadyToAccessQuiz($this->data, $this->user);

        UserQuiz::factory()->inProgress()->entityLessonWithId($this->data->lesson->id)->withUserId($this->user->id)->create();
    }

    public function testAllCorrect()
    {
        $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz', ['answers' => json_decode($this->QuizAnswersGenerator(1, 1, 1, 1)->answers)]);

        $userQuiz = DB::table('user_quizzes')->where('user_id', $this->user->id)->first();
        $this->assertEquals(100, $userQuiz->score);
    }

    public function testSingleFailed()
    {
        $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz', ['answers' => json_decode($this->QuizAnswersGenerator(-1, 1, 1, 1)->answers)]);

        $userQuiz = DB::table('user_quizzes')->where('user_id', $this->user->id)->first();
        $this->assertEquals(75, $userQuiz->score);
    }

    public function testAllMultipleFailed()
    {
        $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz', ['answers' => json_decode($this->QuizAnswersGenerator(1, -1, 1, 1)->answers)]);

        $userQuiz = DB::table('user_quizzes')->where('user_id', $this->user->id)->first();

        $this->assertEquals(75, $userQuiz->score);
    }

    public function testOneMultipleFailed()
    {
        $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz', ['answers' => json_decode($this->QuizAnswersGenerator(1, 1, 1, 1, true)->answers)]);

        $userQuiz = DB::table('user_quizzes')->where('user_id', $this->user->id)->first();

        $this->assertEquals(92, $userQuiz->score);
    }

    public function testAllFailed()
    {
        $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz', ['answers' => json_decode($this->QuizAnswersGenerator(-1, -1, -1, -1)->answers)]);

        $userQuiz = DB::table('user_quizzes')->where('user_id', $this->user->id)->first();
        $this->assertEquals(0, $userQuiz->score);
    }
}
