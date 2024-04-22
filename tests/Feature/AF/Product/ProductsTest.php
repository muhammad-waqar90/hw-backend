<?php

namespace Tests\Feature\AF\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
    }

    public function testProductsDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/af/products');

        $response->assertStatus(200);
    }

    public function testProductsGetRoute()
    {
        $productCount = 2;
        Product::factory()->count($productCount)->create();

        $response = $this->json('GET',  '/api/af/products/');

        $response->assertStatus(200);
        $this->assertEquals(count(json_decode($response->content())->data), $productCount);
    }

    public function testCreateProductPostRoute()
    {
        $category = Category::factory()->withName('Toys')->create();
        $response = $this->json('POST',  '/api/af/products', [
            'category_id'   => $category->id,
            'name'          => Str::random(5),
            'description'   => Str::random(100),
            'img'           => UploadedFile::fake()->image('avatar.jpg'),
            'price'         => 20
        ]);

        $response->assertStatus(200);
    }

    public function testCreateProductWithMetaPostRoute()
    {
        $category = Category::factory()->withName('Toys')->create();
        $mata = ['Author' => 'Dr Israr Ahmed'];

        $response = $this->json('POST',  '/api/af/products', [
            'category_id'   => $category->id,
            'name'          => Str::random(5),
            'description'   => Str::random(100),
            'product_metas' => $mata,
            'img'           => UploadedFile::fake()->image('avatar.jpg'),
            'price'         => 20
        ]);

        $response->assertStatus(200);
    }

    public function testGetProductByIdGetRoute()
    {
        $product = Product::factory()->create();

        $response = $this->json('GET',  '/api/af/products/' . $product->id);

        $response->assertStatus(200);
        $response->assertJsonPath('name', $product->name);
    }

    public function testUpdateProductPostRoute()
    {
        $category = Category::factory()->withName('Toys')->create();
        $product = Product::factory()->create();
        $productName = 'Tafseer';
        $author = 'Dr Israr Ahmed';

        $response = $this->json('POST',  '/api/af/products/' . $product->id, [
            'category_id'   => $category->id,
            'name'          => $productName,
            'description'   => Str::random(100),
            'author'        => $author,
            'img'           => UploadedFile::fake()->image('avatar.jpg'),
            'price'         => 20,
            'is_available'  => false
        ]);

        $response->assertStatus(200);
    }

    public function testDeleteProductDeleteRoute()
    {
        $product = Product::factory()->create();

        $response = $this->json('DELETE',  '/api/af/products/' . $product->id);

        $response->assertStatus(200);
    }
}
