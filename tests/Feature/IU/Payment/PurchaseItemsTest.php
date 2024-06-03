<?php

namespace Tests\Feature\IU\Payment;

use App\Models\Ebook;
use App\Models\PurchaseItem;
use App\Models\User;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\JSONResponseTestTrait;
use App\Traits\Tests\PurchaseItemTestTrait;
use Tests\TestCase;

class PurchaseItemsTest extends TestCase
{
    use CourseTestTrait;
    use JSONResponseTestTrait;
    use PurchaseItemTestTrait;

    private $user;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
        $this->PaymentSeeder($this->user, $this->data->course);
    }

    public function testSetup()
    {
        $response = $this->json('GET', '/api/iu/payments/setup');

        $response->assertStatus(200);
        $this->assertNotNull($response['id']);
    }

    public function testCoursePurchaseItemAvailability()
    {
        PurchaseItem::factory()->withCourseEntityId($this->data->course->id)->create();

        $response = $this->json('GET', '/api/iu/payments');

        $response->assertStatus(200);
        $this->assertEquals(count([$response], true), 1);
    }

    public function testEbookPurchaseItemAvailability()
    {
        $ebook = Ebook::factory()->withlessonId($this->data->lesson2->id)->create();
        PurchaseItem::factory()->withEbookEntityId($ebook->id)->create();

        $response = $this->json('GET', '/api/iu/payments');

        $response->assertStatus(200);
        $this->assertEquals(count([$response], true), 1);
    }

    public function testModuleExamPurchaseItemAvailability()
    {
        PurchaseItem::factory()->withExamEntityId($this->data->courseModule->id)->create();

        $response = $this->json('GET', '/api/iu/payments');

        $response->assertStatus(200);
        $this->assertEquals(count([$response], true), 1);
    }

    public function testLevelExamPurchaseItemAvailability()
    {
        PurchaseItem::factory()->withExamEntityId($this->data->courseLevel->id)->create();

        $response = $this->json('GET', '/api/iu/payments');

        $response->assertStatus(200);
        $this->assertEquals(count([$response], true), 1);
    }

    //refunded Items test
    public function testRefundedCoursePurchaseItemAvailability()
    {
        PurchaseItem::factory()->withCourseEntityId($this->data->course->id)->refunded()->create();

        $response = $this->json('GET', '/api/iu/payments');

        $response->assertStatus(200);
        $this->assertEquals(count([$response], true), 1);
    }

    public function testRefundedEbookPurchaseItemAvailability()
    {
        $ebook = Ebook::factory()->withlessonId($this->data->lesson->id)->create();
        PurchaseItem::factory()->withEbookEntityId($ebook->id)->refunded()->create();

        $response = $this->json('GET', '/api/iu/payments');

        $response->assertStatus(200);
        $this->assertEquals(count([$response], true), 1);
    }

    public function testRefundedModuleExamPurchaseItemAvailability()
    {
        PurchaseItem::factory()->withExamEntityId($this->data->courseModule->id)->refunded()->create();

        $response = $this->json('GET', '/api/iu/payments');

        $response->assertStatus(200);
        $this->assertEquals(count([$response], true), 1);
    }

    public function testRefundedLevelExamPurchaseItemAvailability()
    {
        PurchaseItem::factory()->withExamEntityId($this->data->courseLevel->id)->refunded()->create();

        $response = $this->json('GET', '/api/iu/payments');

        $response->assertStatus(200);
        $this->assertEquals(count([$response], true), 1);
    }
}
