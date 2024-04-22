<?php

namespace Tests\Feature\IU\Course;

use App\Models\User;
use App\Models\Ebook;
use App\Models\EbookAccess;

use App\Traits\Tests\CourseTestTrait;;

use Tests\TestCase;

class EbooksTest extends TestCase
{
    use CourseTestTrait;

    private $user, $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
        Ebook::factory()->withlessonId($this->data->lesson->id)->create();
        EbookAccess::factory()->withUserId($this->user->id)->withCourseModuleId($this->data->courseModule->id)->create();
    }

    public function testCourseLevelEbooksValid()
    {
        $response = $this->json('GET',  '/api/iu/courses/'.$this->data->course->id.'/level/1/ebooks');

        $response->assertOk();
    }

    public function testEbookValid()
    {
        $response = $this->json('GET', '/api/iu/courses/'. $this->data->course->id .'/lessons/' . $this->data->lesson->id . '/ebooks');

        $response->assertOk();
    }

    public function testEbookDismiss()
    {
        $response = $this->json('POST', '/api/iu/courses/'. $this->data->course->id .'/lessons/' . $this->data->lesson->id . '/ebooks/dismiss');

        $response->assertStatus(200);
    }

}