<?php

namespace Tests\Feature\IU\Faq;

use App\Models\User;
use App\Traits\Tests\FaqCategoryTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;
use Tests\TestCase;

class FaqsPublishedTest extends TestCase
{
    use FaqCategoryTestTrait;
    use PermGroupUserTestTrait;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $user = User::factory()->verified()->create();
        $this->data = $this->FaqFactorisationPublished(5);
        $this->actingAs($user);
    }

    public function testAdminSearchFaqsGetValid()
    {
        $response = $this->json('GET', '/api/iu/faqs/');

        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testAdminSearchByIdFaqsGetValid()
    {
        $response = $this->json('GET', '/api/iu/faqs/'.$this->data->faq[0]->id);

        $this->assertEquals($this->data->faq[0]->id, json_decode($response->content())->id);
    }
}
