<?php

namespace Tests\Feature\IU\Course;

use App\Models\User;
use App\Models\UserProgress;
use App\Traits\Tests\CourseTestTrait;
use Tests\TestCase;

class CoursesTest extends TestCase
{
    use CourseTestTrait;

    private $user;

    private $userWithoutCourses;

    private $wrongUser;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->userWithoutCourses = User::factory()->verified()->create();
        $this->wrongUser = User::factory()->institutional()->verified()->create();
        $this->actingAs($this->user);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
    }

    public function testDefaultUserWorking()
    {
        $response = $this->json('GET', '/api/auth/me');

        $response->assertOk();
    }

    public function testDashboardValid()
    {
        $response = $this->json('GET', '/api/iu/courses/dashboard');

        $response->assertOk();
    }

    public function testCoursesAvailableValid()
    {
        $response = $this->json('GET', '/api/iu/courses/available');

        $response->assertOk();
    }

    public function testCoursesAvailableInvalidNotIu()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET', '/api/iu/courses/available');

        $response->assertStatus(403);
    }

    public function testCoursesComingSoonValid()
    {
        $response = $this->json('GET', '/api/iu/courses/coming-soon');

        $response->assertOk();
    }

    public function testCoursesOwnedValid()
    {
        $response = $this->json('GET', '/api/iu/courses/owned');

        $response->assertOk();
    }

    public function testCoursesOwnedValidNoOwnedCourses()
    {
        $response = $this->actingAs($this->userWithoutCourses)->json('GET', '/api/iu/courses/owned');

        $response->assertStatus(200);

        $this->assertEquals(0, count(json_decode($response->content())->data));
    }

    public function testCoursesOwnedInvalidWrongNotIu()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET', '/api/iu/courses/owned');

        $response->assertStatus(403);
    }

    public function testCoursesGetValid()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id);

        $response->assertStatus(200);
    }

    public function testCoursesLevelGetValid()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/level/'.$this->data->courseLevel->value);

        $response->assertStatus(200);
    }

    public function testCoursesLessonGetValid()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id);

        $response->assertStatus(200);
    }

    public function testCoursesLessonGetInvalidNotOwned()
    {
        $response = $this->actingAs($this->userWithoutCourses)->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id);

        $response->assertStatus(403);
    }

    public function testOngoingLessonsGetValidNoOngoingLessons()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/ongoing');

        $response->assertStatus(200);

        $this->assertEquals(0, count(json_decode($response->content())));
    }

    public function testOngoingLessonsGetValid()
    {
        UserProgress::factory()->withUserId($this->user->id)->entityLessonWithId($this->data->lesson->id)->withProgress(20)->create();
        UserProgress::factory()->withUserId($this->user->id)->entityLessonWithId($this->data->lesson2->id)->withProgress(20)->create();

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/ongoing');

        $response->assertStatus(200);

        $this->assertEquals(2, count(json_decode($response->content())));
    }

    public function testModuleLessonsGetValid()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/course-modules/'.$this->data->courseModule->id.'/lessons');

        $response->assertStatus(200);

        $this->assertEquals(2, count(json_decode($response->content())));
    }
}
