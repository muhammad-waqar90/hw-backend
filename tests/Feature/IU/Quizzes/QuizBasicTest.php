<?php

namespace Tests\Feature\IU\Quizzes;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserQuiz;

use App\DataObject\UserProgressData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

use Tests\TestCase;

use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\JSONResponseTestTrait;
use App\Traits\Tests\QuizQATrait;
use App\Traits\Tests\FinishedLessonTrait;

class QuizBasicTest extends TestCase
{
    use CourseTestTrait;
    use JSONResponseTestTrait;
    use QuizQATrait;
    use FinishedLessonTrait;

    private $user, $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        UserProfile::factory()->withUser($this->user->id)->create();
        $this->actingAs($this->user);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
        $this->finishLessonReadyToAccessQuiz($this->data, $this->user);
    }

    public function testLessonVideoFinishedValid()
    {
        $response = $this->json('POST',  '/api/iu/courses/' . $this->data->course->id . '/lessons/' . $this->data->lesson->id . '/video', array('timestamp' => 100, 'updateLessonProgress' => true));
        $response->assertStatus(201);

        $this->assertEquals(Lang::get('iu.successfullyUpdatedVideoProgress'), json_decode($response->content())->message);
    }

    public function testCantAccessLessonPreviousLessonInModuleNotFinishedInvalid()
    {
        $response = $this->json('POST',  '/api/iu/courses/' . $this->data->course->id . '/lessons/' . $this->data->lesson2->id . '/video', array('timestamp' => 100, 'updateLessonProgress' => true));
        $response->assertStatus(403);

        $this->assertEquals(Lang::get('iu.cantAccessLesson'), json_decode($response->content())->errors);
    }

    public function testLessonQuizValid()
    {
        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/lessons/' . $this->data->lesson->id . '/quiz');
        $response->assertOk();
    }

    public function testLessonQuizSubmittedValid()
    {
        UserQuiz::factory()->inProgress()->entityLessonWithId($this->data->lesson->id)->withUserId($this->user->id)->create();

        $response = $this->json('POST',  '/api/iu/courses/' . $this->data->course->id . '/lessons/' . $this->data->lesson->id . '/quiz', array('answers' => json_decode($this->QuizAnswersGenerator(1, 1, 1, 1)->answers)));

        $this->assertEquals(Lang::get('iu.quiz.successfullySubmitted'), json_decode($response->content())->message);
    }

    public function testLessonQuizBeingEvaluatedValid()
    {
        UserQuiz::factory()->submitted()->entityLessonWithId($this->data->lesson->id)->withUserId($this->user->id)->create();

        $response = $this->json('POST',  '/api/iu/courses/' . $this->data->course->id . '/lessons/' . $this->data->lesson->id . '/quiz', array('answers' => json_decode($this->QuizAnswersGenerator(1, 1, 1, 1)->answers)));

        $this->assertEquals(Lang::get('iu.quiz.previousAttemptBeingEvaluated'), json_decode($response->content())->message);
    }

    public function testLessonQuizPreviousValid()
    {
        UserQuiz::factory()->entityLessonWithId($this->data->lesson->id)->withUserId($this->user->id)->withScore(UserProgressData::COMPLETED_PROGRESS)->create();

        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/lessons/' . $this->data->lesson->id . '/quiz/attempt');

        $response->assertOk();
    }

    public function testCourseModulesQuizValid()
    {
        DB::table('user_progress')->where('entity_id', $this->data->courseModule->id)->update(['progress' => UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ]);

        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/course-modules/' . $this->data->courseModule->id . '/quiz');

        $response->assertOk();
    }

    public function testCourseModulesQuizPreviousValid()
    {
        DB::table('user_progress')->where('entity_id', $this->data->courseModule->id)->update(['progress' => UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ]);

        UserQuiz::factory()->entityCourseModuleWithId($this->data->courseModule->id)->withUserId($this->user->id)->withScore(UserProgressData::COMPLETED_PROGRESS)->create();

        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/course-modules/' . $this->data->courseModule->id . '/quiz/attempt');
        $response->assertOk();
    }

    public function testCourseModuleQuizAccessValid(){
        DB::table('user_progress')->where('entity_id', $this->data->courseModule->id)->update(['progress' => UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ]);

        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/course-modules/' . $this->data->courseModule->id . '/quiz/access');
        $response->assertOk();
    }

    public function testCourseLevelsQuizValid()
    {
        DB::table('user_progress')->where('entity_id', $this->data->courseLevel->id)->update(['progress' => UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ]);

        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/course-levels/' . $this->data->courseLevel->id . '/quiz');
        $response->assertOk();
    }

    public function testCourseLevelsQuizPreviousValid()
    {
        DB::table('user_progress')->where('entity_id', $this->data->courseLevel->id)->update(['progress' => UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ]);

        UserQuiz::factory()->entityCourseLevelWithId($this->data->courseLevel->id)->withUserId($this->user->id)->withScore(UserProgressData::COMPLETED_PROGRESS)->create();

        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/course-levels/' . $this->data->courseLevel->id . '/quiz/attempt');

        $response->assertOk();
    }

    public function testModuleQuizSubmittedInValid()
    {
        UserQuiz::factory()->inProgress()->entityCourseModuleWithId($this->data->courseModule->id)->withUserId($this->user->id)->create();

        $response = $this->json('POST',  '/api/iu/courses/' . $this->data->course->id . '/course-modules/' . $this->data->courseModule->id . '/quiz', array('answers' => json_decode($this->QuizAnswersGenerator(1, 1, 1, 1)->answers)));

        $this->assertEquals(Lang::get('iu.quiz.pleaseCompleteLessons'), json_decode($response->content())->errors);
    }

    public function testLevelQuizSubmittedInValid()
    {
        UserQuiz::factory()->inProgress()->entityCourseLevelWithId($this->data->courseLevel->id)->withUserId($this->user->id)->create();

        $response = $this->json('POST',  '/api/iu/courses/' . $this->data->course->id . '/course-levels/' . $this->data->courseLevel->id . '/quiz', array('answers' => json_decode($this->QuizAnswersGenerator(1, 1, 1, 1)->answers)));

        $this->assertEquals(Lang::get('iu.quiz.pleaseCompleteModules'), json_decode($response->content())->errors);
    }
}
