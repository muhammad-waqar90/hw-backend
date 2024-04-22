<?php

namespace Tests\Feature\AF\User;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Ebook;
use App\Models\PurchaseHistory;
use App\Models\PurchaseItem;
use App\Models\UserProfile;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

use Tests\TestCase;

use App\Traits\Tests\PermGroupUserTestTrait;
use App\Traits\Tests\CourseTestTrait;

class UsersTest extends TestCase
{
    use PermGroupUserTestTrait;
    use CourseTestTrait;

    private $user, $admin, $course, $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::factory()->verified()->admin()->create();
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);

        $this->data = $this->CategoryCourseCourseModuleLessonSeeder();

        $category = Category::factory()->create();
        $this->course = Course::factory()->withId($category->id)->withName("Search Course")->create();

        DB::table('course_user')->insert(
            [
            'course_id' =>  $this->course->id,
            'user_id'   =>    $this->user->id,
            ]
        );
    }

    public function testUserGetRoute()
    {
        $response = $this->json('GET', '/api/af/users');

        $response->assertOk();
        $this->assertEquals(1,count(json_decode($response->content())->data));
    }

    public function testUserGetByIdRoute()
    {
        $response = $this->json('GET', '/api/af/users/'.$this->user->id);

        $response->assertOk();
        $this->assertNotNull($response);
    }

    public function testUserCoursesGetRoute()
    {
        $response = $this->json('GET', '/api/af/users/'.$this->user->id.'/courses');

        $response->assertOk();
        $this->assertEquals(1,count(json_decode($response->content())->data));
    }

    public function testUserCourseSearchGetRoute()
    {
        $response = $this->json('GET', '/api/af/users/'.$this->user->id.'/courses?searchText='.$this->course->name);

        $response->assertOk();
        $this->assertEquals(1,count(json_decode($response->content())->data));
    }

    public function testUserPurchasesGetRoute()
    {
        $purchaseHistory = PurchaseHistory::factory()->withUserId($this->user->id)->create();
        PurchaseItem::factory()->withPurchaseHistoryId($purchaseHistory->id)->create();
        $response = $this->json('GET', '/api/af/users/'.$this->user->id.'/purchases');

        $response->assertOk();
        $this->assertEquals(1,count(json_decode($response->content())->data));
    }

    public function testUserUnselectedEbooksForRefund()
    {
        $ebook1 = Ebook::factory()->withlessonId($this->data->lesson->id)->create();
        $purchaseHistory = PurchaseHistory::factory()->withUserId($this->user->id)->create();
        PurchaseItem::factory()->withPurchaseHistoryId($purchaseHistory->id)->withEbookEntityId($ebook1->id)->create();
        $purchaseItem2 = PurchaseItem::factory()->withPurchaseHistoryId($purchaseHistory->id)->withCourseEntityId($this->data->course->id)->create();

        $response = $this->json('GET', '/api/af/users/'.$this->user->id.'/purchases/unselectedEbooks/'.$purchaseItem2->id);

        $response->assertStatus(200);
    }

    public function testUserEnablePutRoute()
    {
        $disabledUser = User::factory()->disabled()->verified()->create();

        $response = $this->json('PUT', '/api/af/users/'.$disabledUser->id.'/enable');

        $response->assertOk();
        $this->assertEquals(Lang::get('auth.enableUser'), json_decode($response->content())->message);
    }

    public function testUserEnablePutRouteInvalid()
    {
        $response = $this->json('PUT', '/api/af/users/'.$this->user->id.'/enable');

        $response->assertStatus(400);
        $this->assertEquals(Lang::get('auth.alreadyEnabled'), json_decode($response->content())->errors);
    }

    public function testUserDisablePutRouteInvalid()
    {
        $disabledUser = User::factory()->disabled()->verified()->create();

        $response = $this->json('PUT', '/api/af/users/'.$disabledUser->id.'/disable');

        $response->assertStatus(400);
        $this->assertEquals(Lang::get('auth.alreadyDisable'), json_decode($response->content())->errors);
    }

    public function testUserDisablePutRoute()
    {
        $response = $this->json('PUT', '/api/af/users/'.$this->user->id.'/disable');

        $response->assertOk();
        $this->assertEquals(Lang::get('auth.disableUser'), json_decode($response->content())->message);
    }

    public function testUserDeleteRoute()
    {
        UserProfile::factory()->withUser($this->user->id)->create();

        $response = $this->json('DELETE', '/api/af/users/'.$this->user->id);
        
        $response->assertOk();
        $this->assertEquals("Account has marked for deletion", json_decode($response->content())->message);
    }

    public function testUserGdprExportRoute()
    {
        UserProfile::factory()->withUser($this->user->id)->create();

        $response = $this->json('POST', '/api/af/users/'.$this->user->id.'/gdpr/export');

        $response->assertStatus(201);
        $this->assertEquals(Lang::get('iu.gdprRequest.successfullyInit'), json_decode($response->content())->message);
    }
}
