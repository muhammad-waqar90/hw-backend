<?php

namespace Tests\Feature\AF\Course;

use App\DataObject\AF\BulkImportEntityTypeData;
use App\DataObject\AF\BulkImportStatusData;
use App\DataObject\AF\BulkImportTypeData;
use App\Models\CourseModule;
use App\Models\User;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModulesTest extends TestCase
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

    public function testCreateModulePostRoute()
    {
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules', [
            'order_id' => 2,
            'name' => 'course module 1',
            'description' => 'course module description',
            'img' => UploadedFile::fake()->image('avatar.jpg'),
            'ebook_price' => 0,
            'video_preview' => 'courseModule.mp4',
            'module_has_exam' => false,
        ]);

        $response->assertStatus(200);
    }

    public function testUpdateModulePostRoute()
    {
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id, [
            'order_id' => 2,
            'name' => 'update course module 1',
            'description' => 'course module description',
            'img' => UploadedFile::fake()->image('avatar.jpg'),
            'ebook_price' => 0,
            'video_preview' => 'courseModule.mp4',
            'module_has_exam' => false,
        ]);

        $response->assertStatus(200);
    }

    public function testSortModulePutRoute()
    {
        $courseModule1 = CourseModule::factory()->withCourseLevelId($this->data->courseLevel->id)->create();
        $courseModule2 = CourseModule::factory()->withCourseLevelId($this->data->courseLevel->id)->create();

        $sortedList = [['id' => $courseModule1->id, 'order_id' => $courseModule2->order_id], ['id' => $courseModule2->id, 'order_id' => $courseModule1->order_id]];

        $response = $this->json('PUT', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/sort', $sortedList);

        $response->assertStatus(200);
    }

    public function testModuleDeleteRoute()
    {
        $response = $this->json('DELETE', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id);

        $response->assertStatus(200);
    }

    public function testMultiModuleDeleteRoute()
    {
        $courseModule1 = CourseModule::factory()->withCourseLevelId($this->data->courseLevel->id)->create();
        $courseModule2 = CourseModule::factory()->withCourseLevelId($this->data->courseLevel->id)->create();

        $response = $this->json('DELETE', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$courseModule1->id.','.$courseModule2->id);

        $response->assertStatus(200);
    }

    public function testBulkImportGetRouteDefault()
    {
        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/quizzes/bulk');
        $response->assertStatus(200);
    }

    public function testBulkImportGetRoute()
    {
        DB::table('bulk_import_statuses')->insert([
            'user_id' => $this->admin->id,
            'course_id' => $this->data->course->id,
            'entity_id' => $this->data->courseModule->id,
            'entity_type' => BulkImportEntityTypeData::MODULE,
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

        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/quizzes/bulk');

        $response->assertStatus(200);
        $this->assertEquals(1, count(json_decode($response->content())->data));
    }

    public function testBulkImportPostRoute()
    {
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/levels/'.$this->data->courseLevel->id.'/modules/'.$this->data->courseModule->id.'/quizzes/bulk', [
            'duration' => 10,
            'sample_size' => 4,
            'price' => 10,
            'file' => UploadedFile::fake()->create('questions', 1, 'xlsx'),
        ]);

        $response->assertStatus(422);
    }
}
