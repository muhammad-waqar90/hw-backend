<?php

namespace Tests\Feature\IU\Course;

use App\Models\User;
use App\Models\LessonNote;

use App\Traits\Tests\CourseTestTrait;;

use Tests\TestCase;

class LessonNotesTest extends TestCase
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
    }

    public function testDefaultLessonNote()
    {
        $response = $this->json('GET',  '/api/iu/courses/'. $this->data->course->id .'/lessons/'. $this->data->lesson->id .'/note');
        $response->assertOk();
    }

    public function testLessonNoteAvailability()
    {
        LessonNote::factory()->withUserId($this->user->id)->withLessonId($this->data->lesson->id)->create();
        $response = $this->json('GET',  '/api/iu/courses/'. $this->data->course->id .'/lessons/'. $this->data->lesson->id .'/note');
        $response->assertOk();
    }

    public function testAddingContentToLessonNote()
    {
        LessonNote::factory()->withUserId($this->user->id)->withLessonId($this->data->lesson->id)->create();
        $response = $this->json('POST',  '/api/iu/courses/'. $this->data->course->id .'/lessons/'. $this->data->lesson->id .'/note',[
            'text' => "Dummy Text",
        ]);
        $response->assertStatus(201);
    }

    public function testAddingHtmlContentToLessonNote()
    {
        LessonNote::factory()->withUserId($this->user->id)->withLessonId($this->data->lesson->id)->create();
        $response = $this->json('POST',  '/api/iu/courses/'. $this->data->course->id .'/lessons/'. $this->data->lesson->id .'/note',[
            'text' => "<p>Hi I am HTML</p>",
        ]);
        $response->assertStatus(201);
    }

    public function testAddingEmptyContentToLessonNote()
    {
        LessonNote::factory()->withUserId($this->user->id)->withLessonId($this->data->lesson->id)->create();
        $response = $this->json('POST',  '/api/iu/courses/'. $this->data->course->id .'/lessons/'. $this->data->lesson->id .'/note',[
            'text' => "",
        ]);
        $response->assertStatus(201);
    }
}