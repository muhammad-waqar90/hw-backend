<?php

namespace Tests\Feature\GU\Course;

use App\DataObject\AF\CourseStatusData;
use App\Models\Course;
use App\Models\Ebook;
use App\Traits\Tests\CourseTestTrait;
use Tests\TestCase;

class CoursesTest extends TestCase
{
    use CourseTestTrait;

    private $user;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
    }

    public function testCoursesGetRoute()
    {
        $response = $this->json('GET', '/api/gu/courses/available');

        $response->assertStatus(200);
    }

    public function testCoursesGetByIdRoute()
    {
        $response = $this->json('GET', '/api/gu/courses/'.$this->data->course->id);

        $response->assertStatus(200);
        $this->assertNotNull($response);
    }

    public function testCourseLevelGetByIdRoute()
    {
        $response = $this->json('GET', '/api/gu/courses/'.$this->data->course->id.'/level/'.$this->data->courseLevel->value);

        $response->assertStatus(200);
        $this->assertNotNull($response);
    }

    public function testCourseEbooksGetRoute()
    {
        Ebook::factory()->withlessonId($this->data->lesson->id)->create();

        $response = $this->json('GET', '/api/gu/courses/'.$this->data->course->id.'/level/'.$this->data->courseLevel->value.'/ebooks');

        $response->assertStatus(200);
        $this->assertNotNull($response);
    }

    public function testComingSoonCourseListDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/gu/courses/coming-soon');
        $response->assertStatus(200);
    }

    public function testComingSoonCourseListGetRoute()
    {
        $courseCount = 2;
        Course::factory($courseCount)->withStatus(CourseStatusData::COMING_SOON)->create();

        $response = $this->json('GET', '/api/gu/courses/coming-soon');

        $response->assertStatus(200);
        $this->assertEquals(count($response['data']), $courseCount);
    }
}
