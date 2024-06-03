<?php

namespace Tests\Feature\AF\Course;

use App\Models\User;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;
use Tests\TestCase;

class LevelsTest extends TestCase
{
    use CourseTestTrait;
    use PermGroupUserTestTrait;

    private $user;

    private $admin;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
    }

    public function testCreateLevelPostRoute()
    {
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels');

        $response->assertStatus(200);
    }

    public function testUpdateLevelPutRoute()
    {
        $response = $this->json('PUT', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id, [
            'name' => 'updated name',
        ]);
        $response->assertStatus(200);
    }

    public function testDeleteLevelDeleteRoute()
    {
        $response = $this->json('DELETE', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id);

        $response->assertStatus(200);
    }
}
