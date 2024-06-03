<?php

namespace Tests\Feature\AF\Notification;

use App\Models\GlobalNotification;
use App\Models\User;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class GlobalNotificationsTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
    }

    public function testGlobalNotificationDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/af/global-notifications');

        $response->assertStatus(200);
    }

    public function testGlobalNotificationGetRoute()
    {
        GlobalNotification::factory(5)->create();

        $response = $this->json('GET', '/api/af/global-notifications');

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testGlobalNotificationGetByIdRoute()
    {
        $globalNotification = GlobalNotification::factory()->create();

        $response = $this->json('GET', '/api/af/global-notifications/'.$globalNotification->id);

        $response->assertStatus(200);
    }

    public function testGlobalNotificationPostRoute()
    {
        $response = $this->json('POST', '/api/af/global-notifications/', [
            'title' => 'title',
            'short_description' => 'description',
            'description' => '<p>body is here </p>',
            'archive_at' => date('Y-m-d', strtotime('+1 years')),
            'show_modal' => 0,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Lang::get('global_notifications.success.created'), json_decode($response->content())->message);
    }

    public function testGlobalNotificationPutRoute()
    {
        $globalNotification = GlobalNotification::factory()->create();

        $response = $this->json('PUT', '/api/af/global-notifications/'.$globalNotification->id, [
            'title' => 'title',
            'short_description' => 'description',
            'description' => '<p> body is here </p>',
            'archive_at' => date('Y-m-d', strtotime('+1 years')),
            'show_modal' => 0,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Lang::get('global_notifications.success.updated'), json_decode($response->content())->message);
    }

    public function testGlobalNotificationDeleteRoute()
    {
        $globalNotification = GlobalNotification::factory()->create();

        $response = $this->json('DELETE', '/api/af/global-notifications/'.$globalNotification->id);

        $response->assertStatus(200);
        $this->assertEquals(Lang::get('global_notifications.success.deleted'), json_decode($response->content())->message);
    }
}
