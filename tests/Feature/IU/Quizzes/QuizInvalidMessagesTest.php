<?php

namespace Tests\Feature\IU\Quizzes;

use App\DataObject\UserProgressData;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserQuiz;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\FinishedLessonTrait;
use App\Traits\Tests\JSONResponseTestTrait;
use App\Traits\Tests\QuizQATrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class QuizInvalidMessagesTest extends TestCase
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
    }

    public function testLessonQuizPreviousNotAttemptedInvalid()
    {
        $this->finishLessonReadyToAccessQuiz($this->data, $this->user);

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz/attempt');

        $this->assertEquals(null, json_decode($response->content())->previousAttempt);
    }

    public function testLessonQuizPleaseWatchVideoInvalid()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz');

        $this->assertEquals(Lang::get('iu.quiz.pleaseWatchVideo'), json_decode($response->content())->errors);
    }

    public function testCourseModuleQuizPleaseCompleteLessons()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/course-modules/'.$this->data->courseModule->id.'/quiz');

        $this->assertEquals(Lang::get('iu.quiz.pleaseCompleteLessons'), json_decode($response->content())->errors);
    }

    public function testCourseLevelQuizPleaseCompleteLessons()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/course-levels/'.$this->data->courseLevel->id.'/quiz');

        $this->assertEquals(Lang::get('iu.quiz.pleaseCompleteModules'), json_decode($response->content())->errors);
    }

    public function testLessonNoQuizInvalid()
    {
        $lessonWithoutQuiz = Lesson::factory()->withCourseId($this->data->course->id)->withCourseModuleId($this->data->courseModule->id)->withOrder(1)->create();

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$lessonWithoutQuiz->id.'/quiz');

        $this->assertEquals(Lang::get('iu.quiz.noQuizFound'), json_decode($response->content())->errors);
    }

    public function testLessonAnswerInvalid()
    {
        $this->finishLessonReadyToAccessQuiz($this->data, $this->user);

        UserQuiz::factory()->inProgress()->entityLessonWithId($this->data->lesson->id)->withUserId($this->user->id)->create();

        $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz', ['answers' => json_decode($this->QuizQuestionGenerator(null, 4, null, null, true)->answers)]);

        $userQuiz = DB::table('user_quizzes')->where('user_id', $this->user->id)->first();
        $this->assertEquals(null, $userQuiz->user_answers);
    }

    public function testLessonNotInitialisedInvalid()
    {
        $this->finishLessonReadyToAccessQuiz($this->data, $this->user);

        $response = $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz', ['answers' => json_decode($this->QuizAnswersGenerator(1, 1, 1, 1)->answers)]);

        $this->assertEquals(Lang::get('iu.quiz.notInitialized'), json_decode($response->content())->errors);
    }

    public function testLessonQuizPreviousStillInProgressInvalid()
    {
        $this->finishLessonReadyToAccessQuiz($this->data, $this->user);

        UserQuiz::factory()->inProgress()->entityLessonWithId($this->data->lesson->id)->withUserId($this->user->id)->create();

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz/attempt');

        $this->assertEquals(Lang::get('iu.quiz.inProgress'), json_decode($response->content())->errors);
    }

    public function testLessonQuizAlreadyPassedValid()
    {
        $this->finishLessonReadyToAccessQuiz($this->data, $this->user);

        UserQuiz::factory()->entityLessonWithId($this->data->lesson->id)->withUserId($this->user->id)->withScore(UserProgressData::COMPLETED_PROGRESS)->create();

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/quiz');

        $this->assertEquals(Lang::get('iu.quiz.alreadyPassed'), json_decode($response->content())->errors);
    }
}
