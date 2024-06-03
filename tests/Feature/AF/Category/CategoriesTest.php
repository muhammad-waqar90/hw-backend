<?php

namespace Tests\Feature\AF\Category;

use App\Models\Category;
use App\Models\User;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;
use Tests\TestCase;

class CategoriesTest extends TestCase
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
        $this->admin = User::factory()->verified()->admin()->create();
        $this->wrongAdmin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
    }

    public function testCategoryGetRouteValid()
    {
        $response = $this->json('GET', '/api/af/categories');

        $response->assertStatus(200);
    }

    public function testRootCategoryGetRouteValid()
    {
        $response = $this->json('GET', '/api/af/categories/root');

        $response->assertStatus(200);

        $this->assertEquals(2, count(json_decode($response->content())));
    }

    public function testChildCategoryFromRootGetRouteValid()
    {
        $response = $this->json('GET', '/api/af/categories/root/'.$this->data->category->id.'/children');

        $response->assertStatus(200);

        $this->assertEquals(4, count(json_decode($response->content())));
    }

    public function testAllCategoryGetRouteForCoursesValid()
    {
        $response = $this->json('GET', '/api/af/categories/filter');

        $response->assertStatus(200);

        $this->assertEquals(6, count(json_decode($response->content())));
    }

    public function testCategoryGetByIdRouteValid()
    {
        $response = $this->json('GET', '/api/af/categories/'.$this->data->category->id);

        $response->assertStatus(200);
    }

    public function testRootCategoryPostRouteValid()
    {
        $response = $this->json('POST', '/api/af/categories/', [
            'name' => 'Root Category',
        ]);

        $response->assertStatus(200);

        $this->assertEquals('Successfully created category', json_decode($response->content())->message);
    }

    public function testChildCategoryPostRouteValid()
    {
        $rootCategory = Category::factory()->create();
        $parentCategory = Category::factory()->childOf($rootCategory)->create();

        $response = $this->json('POST', '/api/af/categories/', [
            'root_category_id' => $rootCategory->id,
            'parent_category_id' => $parentCategory->id,
            'name' => 'Child Category',
        ]);

        $response->assertStatus(200);

        $this->assertEquals('Successfully created category', json_decode($response->content())->message);
    }

    public function testCategoryPutRouteValid()
    {
        $category = Category::factory()->create();
        $rootCategory = Category::factory()->create();
        $parentCategory = Category::factory()->childOf($rootCategory)->create();

        $response = $this->json('PUT', '/api/af/categories/'.$category->id, [
            'root_category_id' => $rootCategory->id,
            'parent_category_id' => $parentCategory->id,
            'name' => 'Updated Category',
        ]);

        $response->assertStatus(200);

        $this->assertEquals('Successfully updated category', json_decode($response->content())->message);
    }

    public function testCategoryPutRouteInvalid()
    {
        $rootCategory = Category::factory()->create();
        $parentCategory = Category::factory()->childOf($rootCategory)->create();

        $response = $this->json('PUT', '/api/af/categories/'.$this->data->category->id, [
            'root_category_id' => $rootCategory->id,
            'parent_category_id' => $parentCategory->id,
            'name' => 'Updated Category',
        ]);

        $response->assertStatus(400);

        $this->assertEquals('Cannot update parent/root category for a category that has child categories attached to it', json_decode($response->content())->errors);
    }

    public function testCategoryDeleteRouteInvalid()
    {
        $response = $this->json('DELETE', '/api/af/categories/'.$this->data->category->id);

        $response->assertStatus(400);

        $this->assertEquals('This category cannot be deleted due to having child categories/courses/products', json_decode($response->content())->errors);
    }

    public function testCategoryDeleteRouteValid()
    {
        $category = Category::factory()->create();

        $response = $this->json('DELETE', '/api/af/categories/'.$category->id);

        $response->assertStatus(200);
    }
}
