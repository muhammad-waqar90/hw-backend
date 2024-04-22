<?php

namespace Tests\Feature\GU\Faq;

use App\Models\FaqCategory;
use App\Models\Faq;

use Tests\TestCase;

class FaqsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function testDefaultCategoriesGetRoute(){
        $response = $this->json('GET',  '/api/gu/faqs/categories');

        $response->assertStatus(200);
    }

    public function testCategoriesGetRoute(){
        FaqCategory::factory(2)->published()->create();

        $response = $this->json('GET',  '/api/gu/faqs/categories');

        $response->assertStatus(200);
        $this->assertEquals(2 , count(json_decode($response->content())));
    }

    public function testCategoriesGetByIdRoute(){
        $faqCategories = FaqCategory::factory(2)->published()->create();
        FaqCategory::factory(2)->withFaqCategoryId($faqCategories[0]->id)->published()->create();

        $response = $this->json('GET',  '/api/gu/faqs/categories/'.$faqCategories[0]->id);

        $response->assertStatus(200);
        $this->assertEquals(2 , count(json_decode($response->content())->data));
    }

    public function testCategoryItemsGetByIdRoute(){
        $faqCategories = FaqCategory::factory(2)->published()->create();
        $faqSubCategories = FaqCategory::factory(2)->withFaqCategoryId($faqCategories[0]->id)->published()->create();
        Faq::factory(5)->withFaqCategoryId($faqSubCategories[0]->id)->published()->create();

        $response = $this->json('GET',  '/api/gu/faqs/categories/'.$faqSubCategories[0]->id.'/items');

        $response->assertStatus(200);
        $this->assertEquals(5 , count(json_decode($response->content())));
    }

    public function testFaqsSearchByQuestionTextGetRoute(){
        $faqCategories = FaqCategory::factory(2)->published()->create();
        $faqSubCategories = FaqCategory::factory(2)->withFaqCategoryId($faqCategories[0]->id)->published()->create();
        Faq::factory(5)->withFaqCategoryId($faqSubCategories[0]->id)->published()->create();

        $response = $this->json('GET',  '/api/gu/faqs?searchText',[
            "searchText" => "question"
        ]);

        $response->assertStatus(200);
        $this->assertEquals(5 , count(json_decode($response->content())->data));
    }

    public function testFaqsSearchBySubCategoryTextGetRoute(){
        $faqCategories = FaqCategory::factory(2)->published()->create();
        $faqSubCategory = FaqCategory::factory()->withFaqCategoryId($faqCategories[0]->id)->withName("subCategory")->published()->create();
        Faq::factory(5)->withFaqCategoryId($faqSubCategory->id)->published()->create();

        $response = $this->json('GET',  '/api/gu/faqs?searchText',[
            "searchText" => "subCategory"
        ]);

        $response->assertStatus(200);
        $this->assertEquals(5 , count(json_decode($response->content())->data));
    }

    public function testFaqsSearchByUbPublishedSubCategoryTextGetRoute(){
        $faqCategories = FaqCategory::factory(2)->published()->create();
        $faqSubCategory = FaqCategory::factory()->withFaqCategoryId($faqCategories[0]->id)->withName("subCategory")->published()->create();
        Faq::factory(5)->withFaqCategoryId($faqSubCategory->id)->create();

        $response = $this->json('GET',  '/api/gu/faqs?searchText',[
            "searchText" => "subCategory"
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0 , count(json_decode($response->content())->data));
    }

    public function testFaqsGetByIdRoute(){
        $faqCategories = FaqCategory::factory(2)->published()->create();
        $faqSubCategory = FaqCategory::factory()->withFaqCategoryId($faqCategories[0]->id)->withName("subCategory")->published()->create();
        $faqs = Faq::factory(5)->withFaqCategoryId($faqSubCategory->id)->published()->create();

        $response = $this->json('GET',  '/api/gu/faqs/'.$faqs[0]->id);

        $response->assertStatus(200);
    }
}