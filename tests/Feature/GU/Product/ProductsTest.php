<?php

namespace Tests\Feature\GU\Product;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function testProductDefaultGetRoute()
    {
        $response = $this->json('GET',  '/api/gu/products/');

        $response->assertStatus(200);
    }

    public function testProductGetRoute()
    {
        $productCount = 2;
        Product::factory()->count($productCount)->create();

        $response = $this->json('GET',  '/api/gu/products/');

        $response->assertStatus(200);
        $this->assertEquals(count($response['products']['data']), $productCount);
    }

    public function testProductByIdGetRoute()
    {
        $product = Product::factory()->create();

        $response = $this->json('GET',  '/api/gu/products/' . $product->id);

        $response->assertStatus(200);
        $this->assertNotNull($response);
    }

    public function testTopBooksGetRoute()
    {
        $productCount = 2;
        Product::factory()->count($productCount)->create();

        $response = $this->json('GET',  '/api/gu/products/top-books');

        $response->assertStatus(200);
        $this->assertEquals(count($response['top-books']['data']), $productCount);
    }

    public function testProductByCategoriesGetRoute()
    {
        $productCount = 2;

        $category = Category::factory()->withName('Tasbeeh')->create();
        //Tasbeeh products
        Product::factory()->count($productCount)->withCategoryId($category->id)->create();

        $category = Category::factory()->withName('Book')->create();
        //Book products
        Product::factory()->count($productCount)->withCategoryId($category->id)->create();

        $response = $this->json('GET',  '/api/gu/products/categories/products-by-category');

        $response->assertStatus(200);
        $this->assertEquals(count($response['categories']), 3);
        foreach ($response['categories'] as $category) {
            if($category["category_name"] == "Book" || $category["category_name"] == "Tasbeeh")
                $this->assertGreaterThanOrEqual(count($category['products']), 2);
        }
    }

    public function testProductByCategoryIdGetRoute()
    {
        $categoryName = 'Tasbeeh';
        $productCount = 2;

        $category = Category::factory()->withName($categoryName)->create();
        Product::factory()->count($productCount)->withCategoryId($category->id)->create();

        $response = $this->json('GET',  '/api/gu/products/categories/' . $category->id);

        $response->assertStatus(200);
        $response->assertJsonPath('category.name', $categoryName);
        $this->assertEquals(count($response['category']['products']), $productCount);
    }
}
