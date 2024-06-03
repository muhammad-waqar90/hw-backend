<?php

namespace Tests\Feature\IU\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testProductsDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/iu/products');

        $response->assertStatus(200);
    }

    public function testProductsGetRoute()
    {
        $productCount = 2;
        Product::factory()->count($productCount)->create();

        $response = $this->json('GET', '/api/iu/products/');

        $response->assertStatus(200);
        $this->assertEquals(count(json_decode($response->content())->products->data), $productCount);
    }

    public function testAvailableBooksGetRoute()
    {
        $productCount = 2;
        Product::factory()->count($productCount)->create();

        $response = $this->json('GET', '/api/iu/products/available-books/');

        $response->assertStatus(200);
        $this->assertEquals(count(json_decode($response->content())->data), $productCount);
    }

    public function testTopBooksGetRoute()
    {
        $productCount = 2;
        Product::factory()->count($productCount)->create();

        $response = $this->json('GET', '/api/iu/products/top-books/');

        $response->assertStatus(200);
        $this->assertEquals(count(json_decode($response->content())->data), $productCount);
    }

    public function testSingleBookGetRoute()
    {
        $product = Product::factory()->create();

        $response = $this->json('GET', '/api/iu/products/single-book/', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('book.name', $product->name);
    }

    public function testSingleProductGetRoute()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->withCategoryId($category->id)->create();

        $response = $this->json('GET', '/api/iu/products/single-product/', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('product.name', $product->name);
    }
}
