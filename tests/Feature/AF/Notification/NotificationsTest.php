<?php

namespace Tests\Feature\AF\Notification;

use App\DataObject\Notifications\NotificationTypeData;
use App\Models\Notification;
use App\Models\User;
use App\Traits\Tests\JSONResponseTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use JSONResponseTestTrait;
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

    public function testNoNotificationsAvailability()
    {
        $response = $this->json('GET', '/api/admins/notifications/me');

        $response->assertStatus(200);

        $this->assertEquals($response['count_unread_notifications'], 0);
    }

    public function testNotificationsAvailability()
    {
        Notification::factory(3)->withUserId($this->admin->id)->withType(NotificationTypeData::SUPPORT_TICKET)->read()->create();
        Notification::factory(3)->withUserId($this->admin->id)->withType(NotificationTypeData::SUPPORT_TICKET)->create();

        $response = $this->json('GET', '/api/admins/notifications/me');

        $response->assertStatus(200);

        $this->assertEquals($response['count_unread_notifications'], 3);
        $this->assertEquals(6, count(json_decode($response->content())->data));
    }

    public function testSingleNotificationRead()
    {
        $notification = Notification::factory()->withUserId($this->admin->id)->withType(NotificationTypeData::SUPPORT_TICKET)->create();
        $response = $this->json('PUT', '/api/notifications/'.$notification->id.'/read');

        $this->assertEquals($response['message'], Lang::get('notifications.success.read'));

        //check read value of notification
        $notification = $response['data'];
        $this->assertEquals($notification['read'], 1);
    }

    public function testSingleAlreadyReadNotificationRead()
    {
        $notification = Notification::factory()->withUserId($this->admin->id)->withType(NotificationTypeData::SUPPORT_TICKET)->read()->create();

        $response = $this->json('PUT', '/api/notifications/'.$notification->id.'/read');

        $this->assertEquals($response['errors'], Lang::get('notifications.errors.alreadyRead'));
    }

    public function testAllNotificationsRead()
    {
        Notification::factory(10)->withUserId($this->admin->id)->withType(NotificationTypeData::SUPPORT_TICKET)->create();

        $response = $this->json('PUT', '/api/admins/notifications/all/read');

        $response->assertStatus(200);

        $this->assertEquals($response['message'], Lang::get('notifications.success.bulkRead'));
    }
}
