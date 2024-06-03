<?php

namespace Tests\Feature\AF\Course;

use App\DataObject\AF\BulkImportEntityTypeData;
use App\DataObject\AF\BulkImportStatusData;
use App\DataObject\AF\BulkImportTypeData;
use App\DataObject\AF\CourseStatusData;
use App\Models\Course;
use App\Models\Product;
use App\Models\Tier;
use App\Models\User;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class CoursesTest extends TestCase
{
    use CourseTestTrait;
    use PermGroupUserTestTrait;

    private $user;

    private $admin;

    private $wrongAdmin;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->admin = User::factory()->verified()->admin()->create();
        $this->wrongAdmin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
    }

    public function testCoursesGetRoute()
    {
        $response = $this->json('GET', '/api/af/courses/filter');

        $response->assertStatus(200);
    }

    public function testCoursesGetByIdRoute()
    {
        $response = $this->json('GET', '/api/af/courses/filter/'.$this->data->course->id);

        $response->assertStatus(200);
        $this->assertNotNull($response);
    }

    public function testCoursesGetRouteInvalid()
    {
        $this->actingAs($this->wrongAdmin);

        $response = $this->json('GET', '/api/af/courses/filter');

        $response->assertStatus(403);
        $this->assertEquals(Lang::get('auth.forbidden'), json_decode($response->content())->errors);
    }

    public function testCoursesGetByIdRouteInvalid()
    {
        $this->actingAs($this->wrongAdmin);

        $response = $this->json('GET', '/api/af/courses/filter/'.$this->data->course->id);

        $response->assertStatus(403);
        $this->assertEquals(Lang::get('auth.forbidden'), json_decode($response->content())->errors);
    }

    public function testCoursesListDetailGetRoute()
    {
        $response = $this->json('GET', '/api/af/courses');

        $response->assertStatus(200);
    }

    public function testCreateCoursePostRoute()
    {
        $tier = Tier::factory()->create();
        $response = $this->json('POST', '/api/af/courses', [
            'category_id' => $this->data->category->id,
            'name' => 'Course name',
            'description' => 'Course Description',
            'price' => '5.4',
            'img' => UploadedFile::fake()->image('avatar.jpg'),
            'number_of_levels' => 2,
            'video_preview' => 'Video Preview',
            'tier_id' => $tier->id,
        ]);

        $response->assertStatus(200);
    }

    public function testCourseDetailGetByIdRoute()
    {
        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id);

        $response->assertStatus(200);
    }

    public function testUpdateCoursePostRoute()
    {
        $tier = Tier::factory()->create();
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id, [
            'category_id' => $this->data->category->id,
            'name' => 'Course name',
            'description' => 'Course Description',
            'price' => '5.4',
            'img' => UploadedFile::fake()->image('avatar.jpg'),
            'number_of_levels' => 2,
            'video_preview' => 'Video Preview',
            'tier_id' => $tier->id,
        ]);

        $response->assertStatus(200);
    }

    public function testValidateCourseGetRoute()
    {
        DB::table('courses')->where('id', $this->data->course->id)->update([
            'status' => CourseStatusData::UNPUBLISHED,
        ]);

        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/validate');

        $response->assertStatus(200);
    }

    public function testCourseUnpublishPutRoute()
    {
        DB::table('courses')->where('id', $this->data->course->id)->update([
            'status' => CourseStatusData::DRAFT,
        ]);

        $response = $this->json('PUT', '/api/af/courses/'.$this->data->course->id.'/status/unpublish');

        $response->assertStatus(200);
    }

    public function testCoursePublishPutRoute()
    {
        DB::table('courses')->where('id', $this->data->course->id)->update([
            'status' => CourseStatusData::UNPUBLISHED,
        ]);
        $response = $this->json('PUT', '/api/af/courses/'.$this->data->course->id.'/status/publish');

        $response->assertStatus(200);
    }

    public function testCourseDraftPutRoute()
    {
        $course = Course::factory()->withId($this->data->category->id)->withStatus(CourseStatusData::PUBLISHED)->create();
        $response = $this->json('PUT', '/api/af/courses/'.$course->id.'/status/draft');

        $response->assertStatus(200);
    }

    public function testCourseComingSoonPutRoute()
    {
        $course = Course::factory()->withId($this->data->category->id)->create();
        $response = $this->json('PUT', '/api/af/courses/'.$course->id.'/status/coming-soon');

        $response->assertStatus(200);
    }

    public function testCourseValidateGetRoute()
    {
        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/validate');

        $response->assertStatus(200);
        $this->assertEquals(count($response['lessons_have_no_quiz']), 4);
    }

    public function testCourseDeleteRoute()
    {
        $course = Course::factory()->withId($this->data->category->id)->create();

        $response = $this->json('DELETE', '/api/af/courses/'.$course->id);

        $response->assertStatus(200);
    }

    public function testEnrolledCourseDeleteRouteInvalid()
    {
        $course = Course::factory()->withId($this->data->category->id)->withStatus(CourseStatusData::PUBLISHED)->create();
        //Enroll course
        DB::table('course_user')->insert([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->json('DELETE', '/api/af/courses/'.$course->id);

        $response->assertStatus(403);
    }

    public function testQuizzesBulkImportGetRouteDefault()
    {
        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/bulk/quizzes');
        $response->assertStatus(200);
    }

    public function testQuizzesBulkImportGetRoute()
    {
        DB::table('bulk_import_statuses')->insert([
            'user_id' => $this->admin->id,
            'course_id' => $this->data->course->id,
            'entity_id' => $this->data->course->id,
            'entity_type' => BulkImportEntityTypeData::COURSE,
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

        $response = $this->json('GET', '/api/af/courses/'.$this->data->course->id.'/bulk/quizzes');
        $response->assertStatus(200);
    }

    public function testQuizzesBulkImportPostRoute()
    {
        $response = $this->json('POST', '/api/af/courses/'.$this->data->course->id.'/bulk/quizzes', [
            'file' => UploadedFile::fake()->image('avatar.jpg'),
        ]);
        $response->assertStatus(422);
    }

    public function testCourseSalaryScaleDiscountEnableStatusPutRoute()
    {
        $response = $this->json('PUT', '/api/af/courses/salary-scale-discounts', [
            'course_id' => $this->data->course->id,
            'is_discounted' => true,
        ]);

        $response->assertStatus(200);
    }

    public function testCourseSalaryScaleDiscountDisableStatusPutRoute()
    {
        $course = Course::factory()->withSalaryScaleDiscount()->create();
        $response = $this->json('PUT', '/api/af/courses/salary-scale-discounts', [
            'course_id' => $course->id,
            'is_discounted' => false,
        ]);

        $response->assertStatus(200);
    }

    public function testUnboundedBooksFilterGetRoute()
    {
        $product = Product::factory()->create();

        $response = $this->json('GET', '/api/af/courses/unboundedBooks/filter', [
            'searchText' => $product->name,
        ]);

        $response->assertStatus(200);
    }
}
