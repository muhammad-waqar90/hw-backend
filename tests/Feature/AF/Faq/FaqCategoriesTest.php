<?php

namespace Tests\Feature\AF\Faq;

use App\Models\User;
use App\Models\FaqCategory;

use Tests\TestCase;

use App\Traits\Tests\FaqCategoryTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;

class FaqCategoriesTest extends TestCase
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

    public function testAdminFaqCategoriesGetValid()
    {
        $response = $this->json('GET', '/api/af/faqs/categories');

        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testAdminFaqCategoriesPostValid()
    {
        $response = $this->json('POST', '/api/af/faqs/categories', array('name' =>  'someName', 'faq_category_id'   =>  null));

        $this->assertEquals('Successfully created faq category', json_decode($response->content())->message);
    }

    public function testAdminFaqRootCategoriesListGetValid()
    {
        $response = $this->json('GET', '/api/af/faqs/categories/root');

        $this->assertEquals(5, count(json_decode($response->content())));
    }

    public function testAdminFaqSubCategoriesListGetValid()
    {
        FaqCategory::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->create();

        $response = $this->json('GET', '/api/af/faqs/categories/sub');

        $this->assertEquals(1, count(json_decode($response->content())));
    }

    public function testAdminFaqCategoryGetValid()
    {
        $response = $this->json('GET', '/api/af/faqs/categories/' . $this->data->faqCategory[0]->id);

        $this->assertEquals($this->data->faqCategory[0]->id, json_decode($response->content())->id);
    }

    public function testAdminFaqCategoryPutValid()
    {
        $response = $this->json('PUT', '/api/af/faqs/categories/' . $this->data->faqCategory[0]->id, array('faq_category_id' =>  null, 'name' =>  'testNewName'));

        $this->assertEquals('Successfully updated faq category', json_decode($response->content())->message);
    }

    public function testAdminFaqCategoryPutInvalidCantMakeRootCategoryIntoSubWhenItsAssociatedWithOthers()
    {
        $newFaqSubCategory = FaqCategory::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->create();

        $response = $this->json('PUT', '/api/af/faqs/categories/' . $this->data->faqCategory[0]->id, array('faq_category_id' =>  $this->data->faqCategory[1]->id, 'name' =>  'testNewName'));

        $this->assertEquals('Cannot update root category to subcategory while it has faq categories associated with it', json_decode($response->content())->errors);
    }

    public function testAdminFaqCategoryDeleteValid()
    {
        $response = $this->json('DELETE', '/api/af/faqs/categories/' . $this->data->faqCategory[0]->id);

        $this->assertEquals('Successfully deleted faq category', json_decode($response->content())->message);
    }

    public function testAdminFaqCategoryPublishValid()
    {
        FaqCategory::factory()->withFaqCategoryId($this->data->faqCategory[0]->id)->published()->create();

        $response = $this->json('PUT', '/api/af/faqs/categories/' . $this->data->faqCategory[0]->id . '/publish');

        $this->assertEquals('Successfully published faq category', json_decode($response->content())->message);
    }

    public function testAdminFaqCategoryPublishInvalidNoPublishedSubcategories()
    {
        $response = $this->json('PUT', '/api/af/faqs/categories/' . $this->data->faqCategory[0]->id . '/publish');

        $this->assertEquals('Cannot publish faq root category with no published subcategories', json_decode($response->content())->errors);
    }

    public function testAdminFaqUnpublishValid()
    {
        $publishedFaqCategory = FaqCategory::factory()->published()->create();

        $response = $this->json('PUT', '/api/af/faqs/categories/' . $publishedFaqCategory->id . '/unpublish');

        $this->assertEquals('Successfully unpublished faq category', json_decode($response->content())->message);
    }
}
