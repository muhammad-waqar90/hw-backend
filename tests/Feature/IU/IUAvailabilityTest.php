<?php

namespace Tests\Feature\IU;

use App\Models\User;
use App\Models\UserProgress;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\JSONResponseTestTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class IUAvailabilityTest extends TestCase
{
    use CourseTestTrait;
    use JSONResponseTestTrait;

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

    public function testCoursesLessonAvailabilityNoProgressValid()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id);

        $response->assertStatus(200);

        $this->assertTrue($this->LessonAvailabilityTest(json_decode($response->content())));
    }

    public function testCoursesLessonAvailabilityLessonFinishedValid()
    {
        UserProgress::factory()->withUserId($this->user->id)->entityLessonWithId($this->data->lesson->id)->withProgress(100)->create();
        UserProgress::factory()->withUserId($this->user->id)->entityCourseModuleWithId($this->data->courseModule->first()->id)->withProgress(50)->create();

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id);

        $response->assertStatus(200);

        $this->assertTrue($this->LessonAvailabilityTest(json_decode($response->content())));
    }

    public function testCoursesLessonAvailabilityLessonHalfFinishedValid()
    {
        UserProgress::factory()->withUserId($this->user->id)->entityLessonWithId($this->data->lesson->id)->withProgress(80)->create();
        UserProgress::factory()->withUserId($this->user->id)->entityCourseModuleWithId($this->data->courseModule->first()->id)->withProgress(40)->create();

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id);

        $response->assertStatus(200);

        $this->assertTrue($this->LessonAvailabilityTest(json_decode($response->content())));
    }

    public function testCoursesLevelLessonAvailabilityValid()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/level/'.$this->data->courseLevel->value);

        $response->assertStatus(200);
    }

    public function testCoursesLevelLessonAvailabilityLessonFinishedValid()
    {
        UserProgress::factory()->withUserId($this->user->id)->entityLessonWithId($this->data->lesson->id)->withProgress(100)->create();
        UserProgress::factory()->withUserId($this->user->id)->entityCourseModuleWithId($this->data->courseModule->first()->id)->withProgress(50)->create();

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/level/'.$this->data->courseLevel->value);

        $response->assertStatus(200);
    }

    public function testNextLevelLessonNotAvailableValid()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/level/'.$this->data->courseLevel->value);
        $response->assertStatus(200);
        $this->assertTrue($this->CourseLevelNotFinishedTest(json_decode($response->content())));

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/level/'.strval((intval($this->data->courseLevel->value)) + 1));
        $response->assertStatus(200);
        $this->assertTrue($this->LessonAvailabilityLevelAllFalseTest(json_decode($response->content())));
    }

    public function testGetNextLevelLessonInvalidReturnsErrorIfCurrentCourseLevelNotFinished()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/level/'.$this->data->courseLevel->value);
        $response->assertStatus(200);
        $this->assertNotEquals(json_decode($response->content())->progress, 100);

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/level/'.strval((intval($this->data->courseLevel->value)) + 1));
        $response->assertStatus(200);
        // get lesson of higher order_id than $this->data->courseLevel->value //
        $lessonLevel2Id = json_decode($response->content())->course_modules[0]->lessons[0]->id;

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$lessonLevel2Id);

        $response->assertStatus(403);
        $this->assertEquals(Lang::get('iu.cantAccessLevel'), json_decode($response->content())->errors);
    }

    public function testGetNextLevelLessonInvalidReturnsErrorIfCurrentLevelLessonNotFinished()
    {
        UserProgress::factory()->withUserId($this->user->id)->entityLessonWithId($this->data->lesson->id)->withProgress(80)->create();
        UserProgress::factory()->withUserId($this->user->id)->entityCourseModuleWithId($this->data->courseModule->first()->id)->withProgress(40)->create();

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id);
        $response->assertStatus(200);

        // get lesson of higher order_id than $this->data->lesson->order_id //
        $lessonOrder2 = DB::table('lessons')->where('course_id', $this->data->course->id)->where('order_id', strval((intval($this->data->lesson->order_id)) + 1))->pluck('id')->first();
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$lessonOrder2);

        $response->assertStatus(403);
        $this->assertEquals(Lang::get('iu.cantAccessLesson'), json_decode($response->content())->errors);
    }
}
