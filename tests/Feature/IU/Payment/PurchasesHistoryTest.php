<?php

namespace Tests\Feature\IU\Payment;

use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\Models\InAppPayment;
use App\Models\User;
use App\Models\PurchaseHistory;

use Tests\TestCase;

class PurchasesHistoryTest extends TestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testDefaultPurchaseHistoryGetRoute(){
        $response = $this->json('GET',  '/api/iu/purchases/history');

        $response->assertStatus(200);
    }

    public function testPurchaseHistoryGetRoute(){
        PurchaseHistory::factory(5)->withUserId($this->user->id)->create();

        $response = $this->json('GET',  '/api/iu/purchases/history');

        $response->assertStatus(200);
        $this->assertEquals(5 , count(json_decode($response->content())->data));
    }

    public function testPurchaseHistoryGetRouteWithInAppPurchases(){
        $inAppPayment = InAppPayment::factory()->create();
        PurchaseHistory::factory(5)->withUserId($this->user->id)->withEntityId($inAppPayment->id)->withEntityType(PurchaseHistoryEntityData::ENTITY_INAPP_PAYMENT)->create();

        $response = $this->json('GET',  '/api/iu/purchases/history');

        $response->assertStatus(200);
        $this->assertEquals(5 , count(json_decode($response->content())->data));
    }
}