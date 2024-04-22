<?php

namespace Tests\Feature\IU\Faq;

use App\Models\User;
use App\Models\FaqCategory;
use App\Models\Faq;

use Tests\TestCase;

use App\Traits\Tests\FaqCategoryTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;

class FaqCategoriesUnpublishedTest extends TestCase
{
    use FaqCategoryTestTrait;
    use PermGroupUserTestTrait;

    private $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $user = User::factory()->verified()->create();
        $this->data = $this->FaqFactorisation(5);
        $this->actingAs($user);
    }

    public function testAdminFaqsCategoriesGetValid()
    {
        FaqCategory::factory()->published()->create();

        $response = $this->json('GET', '/api/iu/faqs/categories');

        $response->assertOk();
    }

    public function testAdminFaqsCategoriesGetValidCantSeeUnpublished()
    {
        $response = $this->json('GET', '/api/iu/faqs/categories');

        $this->assertEquals(0, count(json_decode($response->content())));
    }

    public function testAdminFaqCategoriesByIdGetValid()
    {
        $faqCategory = FaqCategory::factory()->published()->create();
        FaqCategory::factory()->withFaqCategoryId($faqCategory->id)->published()->create();

        $response = $this->json('GET', '/api/iu/faqs/categories/' . $faqCategory->id);

        $this->assertEquals(1, count(json_decode($response->content())->data));
    }

    public function testAdminFaqCategoriesItemsByIdGetValid()
    {
        $faqCategory = FaqCategory::factory()->published()->create();
        Faq::factory()->withFaqCategoryId($faqCategory->id)->published()->create();

        $response = $this->json('GET', '/api/iu/faqs/categories/' . $faqCategory->id . '/items');

        $this->assertEquals(1, count(json_decode($response->content())));
    }
}
