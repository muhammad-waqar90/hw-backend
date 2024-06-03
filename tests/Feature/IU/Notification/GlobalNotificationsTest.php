<?php

namespace Tests\Feature\IU\Notification;

use App\Models\GlobalNotification;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class GlobalNotificationsTest extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testGlobalNotificationGetByIdRoute()
    {
        $globalNotification = GlobalNotification::factory()->create();

        $response = $this->json('GET', '/api/iu/global-notifications/'.$globalNotification->id);

        $response->assertStatus(200);
    }

    public function testGlobalNotificationSeenPutRoute()
    {
        $globalNotification = GlobalNotification::factory()->create();

        $response = $this->json('PUT', '/api/iu/global-notifications/'.$globalNotification->id.'/read');

        $response->assertStatus(200);

        $this->assertEquals(Lang::get('global_notifications.success.read'), json_decode($response->content())->message);
    }

    public function testGlobalNotificationReadPutRoute()
    {
        GlobalNotification::factory(5)->create();

        $response = $this->json('PUT', '/api/iu/global-notifications/modal/read');

        $response->assertStatus(200);

        $this->assertEquals(Lang::get('global_notifications.success.bulkModalRead'), json_decode($response->content())->message);
    }
}
