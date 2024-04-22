<?php

namespace Tests\Feature\AF\Faq;

use App\Models\User;
use App\Models\FaqCategory;
use App\Models\Faq;

use Illuminate\Support\Facades\DB;

use Tests\TestCase;

use App\Traits\Tests\FaqCategoryTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;

class FaqEntriesTest extends TestCase
{
    use FaqCategoryTestTrait;
    use PermGroupUserTestTrait;

    private $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $user = User::factory()->verified()->admin()->create();

        $this->data = $this->FaqFactorisation(5);
        $this->assignFAQCategoryManagementPermissionToUser($user);

        $this->actingAs($user);
    }

    public function testAdminFaqsGetValid()
    {
        $response = $this->json('GET', '/api/af/faqs');

        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testAdminFaqPostValid()
    {
        $newFaqCategory = FaqCategory::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->create();

        $response = $this->json('POST', '/api/af/faqs', array('faq_category_id'   =>  $newFaqCategory->id, 'question' =>  'someQuestion', 'short_answer' =>  'someShortAnswer', 'answer' =>  'someTestingLongerAnswer'));

        $this->assertEquals('Successfully created faq', json_decode($response->content())->message);
    }

    public function testAdminFaqGetSubCategoryListValid()
    {
        $faqSubCategory = FaqCategory::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->create();

        $response = $this->json('GET', '/api/af/faqs/categories/sub');

        $this->assertEquals($faqSubCategory->id, json_decode($response->content())[0]->id);
    }

    public function testAdminFaqGetValid()
    {
        $response = $this->json('GET', '/api/af/faqs/' . $this->data->faq[0]->id);

        $this->assertEquals($this->data->faq[0]->id, json_decode($response->content())->id);
    }

    public function testAdminFaqPutValid()
    {
        $newFaqCategory = FaqCategory::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->create();

        $response = $this->json('PUT', '/api/af/faqs/' . $this->data->faq[0]->id, array('faq_category_id' =>  $newFaqCategory->id, 'question' =>  'testPutQuestion', 'short_answer' =>  'testShortAnswer', 'answer' =>  'testAnswer'));

        $this->assertEquals('Successfully updated faq', json_decode($response->content())->message);
    }

    public function testAdminFaqPutInvalidFaqCategory()
    {
        FaqCategory::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->create();

        $response = $this->json('PUT', '/api/af/faqs/' . $this->data->faq[0]->id, array('faq_category_id' =>  $this->data->faqCategory[0]->id, 'question' =>  'testPutQuestion', 'short_answer' =>  'testShortAnswer', 'answer' =>  'testAnswer'));

        $this->assertEquals('Invalid faq category', json_decode($response->content())->errors);
    }

    public function testAdminFaqPutValidSubFaqCategory()
    {
        $newFaqSubCategory = FaqCategory::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->create();
        $newFaqSubSubCategory = FaqCategory::factory()->withFaqCategoryId($newFaqSubCategory->id)->create();

        $response = $this->json('PUT', '/api/af/faqs/' . $this->data->faq[0]->id, array('faq_category_id' =>  $newFaqSubSubCategory->id, 'question' =>  'testPutQuestion', 'short_answer' =>  'testShortAnswer', 'answer' =>  'testAnswer'));

        $this->assertEquals('Successfully updated faq', json_decode($response->content())->message);
    }

    public function testAdminFaqDeleteValid()
    {
        $response = $this->json('DELETE', '/api/af/faqs/' . $this->data->faq[0]->id);

        $this->assertEquals('Successfully deleted faq', json_decode($response->content())->message);
    }

    public function testAdminFaqPublishValid()
    {
        $response = $this->json('PUT', '/api/af/faqs/' . $this->data->faq[0]->id . '/publish');

        $this->assertEquals('Successfully published faq', json_decode($response->content())->message);
    }

    public function testAdminFaqUnpublishValid()
    {
        $publishedFaq = Faq::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->published()->create();

        $response = $this->json('PUT', '/api/af/faqs/' . $publishedFaq->id . '/unpublish');

        $this->assertEquals('Successfully unpublished faq', json_decode($response->content())->message);
    }

    public function testAdminFaqUnpublishByUnpublishingRootCategoryValid()
    {
        $rootFaqCategory = FaqCategory::factory()->published()->create();
        $subFaqCategory = FaqCategory::factory()->withFaqCategoryId($rootFaqCategory->id)->published()->create();

        $faqInSubCategory = Faq::factory()->withFaqCategoryId($subFaqCategory->id)->published()->create();

        $this->json('PUT', '/api/af/faqs/' . $faqInSubCategory->id . '/unpublish');

        $isPublished = DB::table('faq_categories')->where('id', $rootFaqCategory->id)->value('published');

        $this->assertEquals(0, $isPublished);
    }
}
