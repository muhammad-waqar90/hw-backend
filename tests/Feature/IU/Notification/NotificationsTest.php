<?php

namespace Tests\Feature\IU\Notification;

use App\DataObject\Notifications\NotificationTypeData;
use App\Models\Notification;
use App\Models\User;
use App\Traits\Tests\CertificateTestTrait;
use App\Traits\Tests\JSONResponseTestTrait;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use CertificateTestTrait;
    use JSONResponseTestTrait;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testNoNotificationsAvailability()
    {
        $response = $this->json('GET', '/api/iu/notifications/me');

        $response->assertStatus(200);
    }

    public function testSupportTicketNotificationsAvailability()
    {
        Notification::factory()->withUserId($this->user->id)->withType(NotificationTypeData::SUPPORT_TICKET)->create();

        $response = $this->json('GET', '/api/iu/notifications/me');

        $response->assertStatus(200);

        //check count & type of notification
        $notification = $response['data'];
        $this->assertEquals(count($notification), 1);
        $this->assertEquals($notification[0]['type'], NotificationTypeData::SUPPORT_TICKET);
    }

    public function testGlobalNotificationsAvailability()
    {
        Notification::factory()->withUserId($this->user->id)->withType(NotificationTypeData::GLOBAL)->create();

        $response = $this->json('GET', '/api/iu/notifications/me');

        $response->assertStatus(200);

        //check count & type of notification
        $notification = $response['data'];
        $this->assertEquals(count($notification), 1);
        $this->assertEquals($notification[0]['type'], NotificationTypeData::GLOBAL);
    }

    public function testCertificateNotificationsAvailability()
    {
        Notification::factory()->withUserId($this->user->id)->withType(NotificationTypeData::CERTIFICATE)->create();

        $response = $this->json('GET', '/api/iu/notifications/me');

        $response->assertStatus(200);

        //check count & type of notification
        $notification = $response['data'];
        $this->assertEquals(count($notification), 1);
        $this->assertEquals($notification[0]['type'], NotificationTypeData::CERTIFICATE);
    }

    public function testAllTypesNotificationsAvailability()
    {
        $ticketNotification = Notification::factory()->withUserId($this->user->id)->withType(NotificationTypeData::SUPPORT_TICKET)->create();
        $globalNotification = Notification::factory()->withUserId($this->user->id)->withType(NotificationTypeData::GLOBAL)->create();
        $certificateNotification = Notification::factory()->withUserId($this->user->id)->withType(NotificationTypeData::CERTIFICATE)->create();

        $response = $this->json('GET', '/api/iu/notifications/me');

        //check type of notification
        $notifications = $response['data'];
        $ticketNotification = $this->findItemInArray($notifications, $ticketNotification);
        $globalNotification = $this->findItemInArray($notifications, $globalNotification);
        $certificateNotification = $this->findItemInArray($notifications, $certificateNotification);

        $this->assertEquals($ticketNotification['type'], NotificationTypeData::SUPPORT_TICKET);
        $this->assertEquals($globalNotification['type'], NotificationTypeData::GLOBAL);
        $this->assertEquals($certificateNotification['type'], NotificationTypeData::CERTIFICATE);
    }

    public function testNextPageUrls()
    {
        Notification::factory(20)->withUserId($this->user->id)->withType(NotificationTypeData::SUPPORT_TICKET)->create();

        $response = $this->json('GET', '/api/iu/notifications/me');

        $nextPageUrl = $response['next_page_url'];
        $this->assertNotNull($nextPageUrl);
    }

    public function testSingleNotificationSeen()
    {
        $notification = Notification::factory()->withUserId($this->user->id)->withType(NotificationTypeData::CERTIFICATE)->create();

        $response = $this->json('PUT', '/api/notifications/'.$notification->id.'/read');

        $this->assertEquals($response['message'], Lang::get('notifications.success.read'));

        //check read value of notification
        $notification = $response['data'];
        $this->assertEquals($notification['read'], 1);
    }

    public function testSingleAlreadySeenNotificationSeen()
    {
        $notification = Notification::factory()->withUserId($this->user->id)->withType(NotificationTypeData::CERTIFICATE)->read()->create();

        $response = $this->json('PUT', '/api/notifications/'.$notification->id.'/read');

        $this->assertEquals($response['errors'], Lang::get('notifications.errors.alreadyRead'));
    }

    public function testAllNotificationsSeen()
    {
        Notification::factory(10)->withUserId($this->user->id)->withType(NotificationTypeData::SUPPORT_TICKET)->create();
        Notification::factory(10)->withUserId($this->user->id)->withType(NotificationTypeData::GLOBAL)->create();
        Notification::factory(5)->withUserId($this->user->id)->withType(NotificationTypeData::CERTIFICATE)->create();

        $response = $this->json('PUT', '/api/iu/notifications/all/read');

        $response->assertStatus(200);
        $this->assertEquals($response['message'], Lang::get('notifications.success.bulkRead'));
    }
}
