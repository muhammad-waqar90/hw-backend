<?php

namespace Tests\Feature\IU;

use App\Models\User;

use Tests\TestCase;

use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\JSONResponseTestTrait;

class IUBasicTest extends TestCase
{
    use CourseTestTrait;
    use JSONResponseTestTrait;

    private $wrongUser, $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $user = User::factory()->verified()->create();
        $this->wrongUser = User::factory()->verified()->create();
        $this->actingAs($user);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($user);
    }

    public function testDefaultUserWorking()
    {
        $response = $this->json('GET',  '/api/auth/me');
        $response->assertOk();
    }

    public function testCoursesAvailableValid()
    {
        $response = $this->json('GET',  '/api/iu/courses/available');
        $response->assertOk();
    }

    public function testCoursesAvailableInvalidNotIu()
    {
        $user = User::factory()->verified()->create([
            'role_id' => 2,
        ]);

        $response = $this->actingAs($user)->json('GET',  '/api/iu/courses/available');
        $response->assertStatus(403);
    }

    public function testCoursesOwnedValid()
    {
        $response = $this->json('GET',  '/api/iu/courses/owned');
        $response->assertOk();
        $this->assertNotEquals(count(json_decode($response->content())->data), 0);
    }

    public function testCoursesOwnedValidNoOwnedCourses()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET',  '/api/iu/courses/owned');
        $response->assertOk();
        $this->assertEquals(count(json_decode($response->content())->data), 0);
    }

    public function testCoursesOwnedInvalidWrongNotIu()
    {
        $wrongUser = User::factory()->verified()->create([
            'role_id' => 2,
        ]);

        $response = $this->actingAs($wrongUser)->json('GET',  '/api/iu/courses/owned');
        $response->assertStatus(403);
    }

    public function testCoursesGetValid()
    {
        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id);

        $response->assertStatus(200);
    }

    public function testCoursesLevelGetValid()
    {
        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/level/' . $this->data->courseLevel->value);

        $response->assertStatus(200);
    }

    public function testCoursesLessonGetValid()
    {
        $response = $this->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/lessons/' . $this->data->lesson->id);

        $response->assertStatus(200);
    }

    public function testCoursesLessonGetInvalidNotOwned()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET',  '/api/iu/courses/' . $this->data->course->id . '/lessons/' . $this->data->lesson->id);

        $response->assertStatus(403);
    }
}
