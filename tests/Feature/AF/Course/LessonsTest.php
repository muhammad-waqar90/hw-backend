<?php

namespace Tests\Feature\AF\Course;

use App\DataObject\AF\BulkImportEntityTypeData;
use App\DataObject\AF\BulkImportStatusData;
use App\DataObject\AF\BulkImportTypeData;
use App\Models\Ebook;
use App\Models\Lesson;
use App\Models\LessonFaq;
use App\Models\User;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LessonsTest extends TestCase
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

    public function testCreateLessonPostRoute()
    {
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons', [
            'order_id' => 2,
            'name' => 'course module 1',
            'description' => 'course module description',
            'img' => UploadedFile::fake()->image('avatar.jpg'),
            'published' => true,
            'video' => 'courseModule.mp4',
        ]);

        $response->assertStatus(200);
    }

    public function testUpdateLessonPostRoute()
    {
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id, [
            'order_id' => 2,
            'name' => 'update course module 1',
            'description' => 'course module description',
            'img' => UploadedFile::fake()->image('avatar.jpg'),
            'published' => true,
            'video' => 'courseModule.mp4',
        ]);

        $response->assertStatus(200);
    }

    public function testSortLessonPutRoute()
    {
        $lesson1 = Lesson::factory()->withCourseId($this->data->course->id)->withCourseModuleId($this->data->courseModule->id)->withOrder(1)->create();
        $lesson2 = Lesson::factory()->withCourseId($this->data->course->id)->withCourseModuleId($this->data->courseModule->id)->withOrder(2)->create();

        $sortedList = [['id' => $lesson1->id, 'order_id' => $lesson2->order_id], ['id' => $lesson2->id, 'order_id' => $lesson1->order_id]];

        $response = $this->json('PUT', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/sort', $sortedList);

        $response->assertStatus(200);
    }

    public function testLessonDeleteRoute()
    {
        $response = $this->json('DELETE', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id);

        $response->assertStatus(200);
    }

    public function testMultiLessonDeleteRoute()
    {
        $lesson1 = Lesson::factory()->withCourseId($this->data->course->id)->withCourseModuleId($this->data->courseModule->id)->withOrder(1)->create();
        $lesson2 = Lesson::factory()->withCourseId($this->data->course->id)->withCourseModuleId($this->data->courseModule->id)->withOrder(2)->create();

        $response = $this->json('DELETE', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$lesson1->id.','.$lesson2->id);

        $response->assertStatus(200);
    }

    public function testEbooksGetRoute()
    {
        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id.'/ebook?with_src=0');

        $response->assertStatus(200);
    }

    public function testEbookPostRoute()
    {
        Ebook::factory()->withlessonId($this->data->lesson->id)->create();
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id.'/ebook', [
            'content' => Str::random(10),
        ]);

        $response->assertStatus(400);
    }

    public function testEbookUpdatePostRoute()
    {
        $ebook = Ebook::factory()->withlessonId($this->data->lesson->id)->create();
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id.'/ebook/'.$ebook->id, [
            'content' => Str::random(10),
        ]);

        $response->assertStatus(200);
    }

    public function testEbookDeleteRoute()
    {
        $ebook = Ebook::factory()->withlessonId($this->data->lesson->id)->create();
        $response = $this->json('DELETE', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id.'/ebook/'.$ebook->id);

        $response->assertStatus(200);
    }

    public function testLessonFaqsGetDefaultRoute()
    {
        $response = $this->json('GET', '/api/af/lesson-faqs/'.$this->data->lesson->id);

        $response->assertStatus(200);
    }

    public function testLessonFaqsGetRoute()
    {
        LessonFaq::factory(2)->withlessonId($this->data->lesson->id)->create();

        $response = $this->json('GET', '/api/af/lesson-faqs/'.$this->data->lesson->id);

        $response->assertStatus(200);
        $this->assertEquals(2, count(json_decode($response->content())));
    }

    public function testLessonFaqPostRoute()
    {
        $response = $this->json('POST', '/api/af/lesson-faqs', [
            'lesson_id' => $this->data->lesson->id,
            'question' => 'question text',
            'answer' => 'answer text',
        ]);

        $response->assertStatus(200);
    }

    public function testLessonFaqPutRoute()
    {
        $lessonFaq = LessonFaq::factory()->withlessonId($this->data->lesson->id)->create();
        $response = $this->json('PUT', '/api/af/lesson-faqs/'.$lessonFaq->id, [
            'lesson_id' => $this->data->lesson->id,
            'question' => 'question text',
            'answer' => 'answer text',
        ]);

        $response->assertStatus(200);
    }

    public function testLessonFaqDeleteRoute()
    {
        $lessonFaq = LessonFaq::factory()->withlessonId($this->data->lesson->id)->create();
        $response = $this->json('DELETE', '/api/af/lesson-faqs/'.$lessonFaq->id);

        $response->assertStatus(200);
    }

    public function testBulkImportGetRouteDefault()
    {
        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id.'/quizzes/bulk');
        $response->assertStatus(200);
    }

    public function testBulkImportGetRoute()
    {
        DB::table('bulk_import_statuses')->insert([
            'user_id' => $this->admin->id,
            'course_id' => $this->data->course->id,
            'entity_id' => $this->data->lesson->id,
            'entity_type' => BulkImportEntityTypeData::LESSON,
            'type' => BulkImportTypeData::QUIZ,
            'status' => BulkImportStatusData::COMPLETED,
            'errors' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('admin_profiles')->insert([
            'user_id' => $this->admin->id,
            'email' => 'test@test.com',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id.'/quizzes/bulk');

        $response->assertStatus(200);
        $this->assertEquals(1, count(json_decode($response->content())->data));
    }

    public function testBulkImportPostRoute()
    {
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/lessons/'.$this->data->lesson->id.'/quizzes/bulk', [
            'duration' => 10,
            'sample_size' => 4,
            'price' => '',
            'file' => UploadedFile::fake()->create('questions', 1, 'xlsx'),
        ]);

        $response->assertStatus(422);
    }
}
