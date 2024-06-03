<?php

namespace Tests\Feature\AF\Ticketing;

use App\DataObject\Tickets\TicketCategoryData;
use App\DataObject\Tickets\TicketStatusData;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\UserProfile;
use App\Traits\Tests\CourseTestTrait;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class TicketsWorkflowTest extends TestCase
{
    use CourseTestTrait;
    use PermGroupUserTestTrait;

    private $admin;

    private $user;

    private $otherAdmin;

    private $tickets;

    private $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::factory()->verified()->admin()->create();
        $this->otherAdmin = User::factory()->verified()->admin()->create();
        $this->user = User::factory()->verified()->create();
        UserProfile::factory()->withUser($this->user->id)->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
        $this->assignAllPermissionToUser($this->otherAdmin);
        $this->course = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
        $this->tickets = Ticket::factory(5)->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
    }

    public function testAdminSeeAllUnclaimedTicketsValid()
    {
        $response = $this->json('GET', 'api/af/tickets');

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testAdminMeRouteValid()
    {
        Ticket::factory(5)->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->json('GET', 'api/af/tickets/me');

        $response->assertStatus(200);
    }

    public function testAdminDontSeeTicketsClaimedByOtherAdminValid()
    {
        Ticket::factory(5)->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->otherAdmin)->withUser($this->user)->create();

        $response = $this->json('GET', 'api/af/tickets/me');

        $response->assertStatus(200);
        $this->assertEquals(0, count(json_decode($response->content())->data));
    }

    public function testAdminClaimUnclaimedTicketValid()
    {
        $response = $this->json('PUT', 'api/af/tickets/'.$this->tickets[0]->id.'/claim');

        $response->assertStatus(200);
        $this->assertEquals('Successfully claimed ticket', json_decode($response->content())->message);
    }

    public function testAdminTryToClaimNonexistentTicketInvalid()
    {
        $response = $this->json('PUT', 'api/af/tickets/5555555/claim');

        $response->assertStatus(404);
        $this->assertEquals(Lang::get('general.notFound'), json_decode($response->content())->errors);
    }

    public function testAdminTryToClaimClaimedByAdminTicketInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/claim');

        $response->assertStatus(400);
        $this->assertEquals('You have already claimed the ticket', json_decode($response->content())->errors);
    }

    public function testAdminTryToClaimClaimedByOtherAdminTicketInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->otherAdmin)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/claim');

        $response->assertStatus(400);
        $this->assertEquals('Ticket already claimed by somebody else', json_decode($response->content())->errors);
    }

    public function testAdminTryToClaimResolvedTicketInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::CONTENT)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/claim');

        $response->assertStatus(400);
        $this->assertEquals('Cannot claim resolved ticket', json_decode($response->content())->errors);
    }

    public function testAdminTryToClaimReopenedTicketValid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::REOPENED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/claim');

        $response->assertStatus(200);
        $this->assertEquals('Successfully claimed ticket', json_decode($response->content())->message);
    }

    public function testAdminTryToClaimTicketWithoutPermissionInvalid()
    {
        $this->assignSystemPermissionToUser($this->admin);
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::CONTENT)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/claim');

        $response->assertStatus(400);
        $this->assertEquals('Permission missing to claim this ticket', json_decode($response->content())->errors);
    }

    public function testSeenByAdmin()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $this->assertEquals(0, DB::table('tickets')->where('id', $ticket->id)->pluck('seen_by_admin')->first());

        $this->json('GET', 'api/af/tickets/'.$ticket->id);

        $this->assertEquals(1, DB::table('tickets')->where('id', $ticket->id)->pluck('seen_by_admin')->first());
    }

    public function testSeenByUser()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $this->assertEquals(0, DB::table('tickets')->where('id', $ticket->id)->pluck('seen_by_user')->first());

        $this->actingAs($this->user)->json('GET', 'api/iu/tickets/'.$ticket->id);

        $this->assertEquals(1, DB::table('tickets')->where('id', $ticket->id)->pluck('seen_by_user')->first());
    }

    public function testGuestTicketSeenByAdmin()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withEmail('guest@test.com')->create();

        $this->assertEquals(0, DB::table('tickets')->where('id', $ticket->id)->pluck('seen_by_admin')->first());

        $this->json('GET', 'api/af/tickets/'.$ticket->id);

        $this->assertEquals(1, DB::table('tickets')->where('id', $ticket->id)->pluck('seen_by_admin')->first());
    }

    public function testUnclaimTicketWithStatusInProgressValid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/unclaim');

        $this->assertEquals('Successfully unclaimed ticket', json_decode($response->content())->message);
        $this->assertEquals(1, DB::table('tickets')->where('id', $ticket->id)->pluck('ticket_status_id')->first());
    }

    public function testUnclaimTicketWithStatusResolvedInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/unclaim');

        $this->assertEquals('Forbidden', json_decode($response->content())->errors);
    }

    public function testUnclaimTicketWhichIsClaimedByOtherAdminInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->otherAdmin)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/unclaim');

        $this->assertEquals('Forbidden', json_decode($response->content())->errors);
    }

    public function testUnclaimTicketWhichIsNotClaimedByAdminInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/unclaim');

        $this->assertEquals('Forbidden', json_decode($response->content())->errors);
    }

    public function testResolveTickedByAdminValid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/resolve');

        $this->assertEquals(Lang::get('tickets.successfullyResolvedTicket'), json_decode($response->content())->message);
        $this->assertEquals(3, DB::table('tickets')->where('id', $ticket->id)->pluck('ticket_status_id')->first());
    }

    public function testResolveTickedByOtherAdminInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->actingAs($this->otherAdmin)->json('PUT', 'api/af/tickets/'.$ticket->id.'/resolve');

        $this->assertEquals('Forbidden', json_decode($response->content())->errors);
    }

    public function testResolveTickedByUserValid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->actingAs($this->user)->json('PUT', 'api/iu/tickets/'.$ticket->id.'/resolve');

        $this->assertEquals(Lang::get('tickets.successfullyResolvedTicket'), json_decode($response->content())->message);
        $this->assertEquals(3, DB::table('tickets')->where('id', $ticket->id)->pluck('ticket_status_id')->first());
    }

    public function testResolveTickedByOtherUserInvalid()
    {
        $otherUser = User::factory()->verified()->create();
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->actingAs($otherUser)->json('PUT', 'api/iu/tickets/'.$ticket->id.'/resolve');

        $this->assertEquals('Forbidden', json_decode($response->content())->errors);
    }

    public function testReopenByUserResolvedTicketValid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::CONTENT)->withUser($this->user)->create();

        $response = $this->actingAs($this->user)->json('PUT', 'api/iu/tickets/'.$ticket->id.'/reopen');

        // dd(json_decode($response->content())->message);
        $this->assertEquals(Lang::get('tickets.successfullyReopenedTicket'), json_decode($response->content())->message);
    }

    public function testReopenByOtherUserResolvedTicketInvalid()
    {
        $otherUser = User::factory()->verified()->create();
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::CONTENT)->withUser($this->user)->create();

        $response = $this->actingAs($otherUser)->json('PUT', 'api/iu/tickets/'.$ticket->id.'/reopen');

        $this->assertEquals('Forbidden', json_decode($response->content())->errors);
    }

    public function testOnHoldTicketValid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/on-hold');

        $this->assertEquals(Lang::get('Successfully put ticket on hold'), json_decode($response->content())->message);
    }

    public function testOnHoldByOtherUserTicketInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withUser($this->user)->create();

        $response = $this->actingAs($this->otherAdmin)->json('PUT', 'api/af/tickets/'.$ticket->id.'/on-hold');

        $this->assertEquals('Forbidden', json_decode($response->content())->errors);
    }

    public function testOnHoldUnclaimedTicketInvalid()
    {
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::CONTENT)->withUser($this->user)->create();

        $response = $this->json('PUT', 'api/af/tickets/'.$ticket->id.'/on-hold');

        $this->assertEquals('Forbidden', json_decode($response->content())->errors);
    }

    public function testChangeCategoryOfUnclaimedTicketValid()
    {
        $response = $this->json('PUT', 'api/af/tickets/'.$this->tickets[0]->id.'/categories', ['categoryId' => 2]);

        $this->assertEquals('Successfully updated ticket category', json_decode($response->content())->message);
    }

    public function testChangeCategoryOfUnclaimedTicketToSameCategoryInvalid()
    {
        $response = $this->json('PUT', 'api/af/tickets/'.$this->tickets[0]->id.'/categories', ['categoryId' => 1]);

        $this->assertEquals('Ticket already has the selected category', json_decode($response->content())->errors);
    }

    public function testChangeCategoryOfUnclaimedTicketToNonexistentCategoryInvalid()
    {
        $response = $this->json('PUT', 'api/af/tickets/'.$this->tickets[0]->id.'/categories', ['categoryId' => 999]);

        $response->assertStatus(422);
        $this->assertEquals('The selected category id is invalid.', json_decode($response->content())->message);
    }

    public function testCheckMessagesAsUserForUnclaimedTicketValid()
    {
        $message = 'I need help please';

        $response = $this->actingAs($this->user)->json('POST', 'api/iu/tickets/'.$this->tickets[0]->id.'/messages', [
            'message' => $message,
            'assets' => [],
        ]);

        $response->assertOk();
        $this->assertEquals($message, json_decode($response->content())->data->message[0]->message);
    }

    public function testCheckMessagesAsUserForReopenedTicketValid()
    {
        $message = 'I need help please';
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::REOPENED)->withCategoryId(TicketCategoryData::CONTENT)->withUser($this->user)->create();

        $response = $this->actingAs($this->user)->json('POST', 'api/iu/tickets/'.$ticket->id.'/messages', [
            'message' => $message,
            'assets' => [],
        ]);

        $response->assertOk();
        $this->assertEquals($message, json_decode($response->content())->data->message[0]->message);
    }

    public function testCheckMessagesAsAdminForClaimedTicketValid()
    {
        $message = 'Try this';
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::CONTENT)->withAdmin($this->admin)->withUser($this->user)->create();

        $response = $this->json('POST', 'api/af/tickets/'.$ticket->id.'/messages', [
            'message' => $message,
            'assets' => [],
        ]);

        $response->assertOk();
        $this->assertEquals($message, json_decode($response->content())->data->message[0]->message);
    }

    public function testGetALotOfMessagesAsAdminForUnclaimedTicketLastPageViewValid()
    {
        TicketMessage::factory(301)->withUserId($this->user->id)->withTicketId($this->tickets[0]->id)->create();

        $response = $this->json('GET', 'api/af/tickets/'.$this->tickets[0]->id.'?page=21');

        $response->assertOk();
        $this->assertEquals(1, count(json_decode($response->content())->messages->data));
    }

    public function testAdminCanSeeAdminOnlySystemMessagesValid()
    {
        TicketMessage::factory(5)->withUserId($this->user->id)->withTicketId($this->tickets[0]->id)->create();
        TicketMessage::factory(5)->withTicketId($this->tickets[0]->id)->withAdminOnlySystemMessage()->create();

        $response = $this->json('GET', 'api/af/tickets/'.$this->tickets[0]->id);

        $response->assertOk();
        $this->assertEquals(10, count(json_decode($response->content())->messages->data));
    }

    public function testUserCantSeeAdminOnlySystemMessagesValid()
    {
        TicketMessage::factory(5)->withUserId($this->user->id)->withTicketId($this->tickets[0]->id)->create();
        TicketMessage::factory(5)->withTicketId($this->tickets[0]->id)->withAdminOnlySystemMessage()->create();

        $response = $this->actingAs($this->user)->json('GET', 'api/iu/tickets/'.$this->tickets[0]->id);

        $response->assertOk();
        $this->assertEquals(5, count(json_decode($response->content())->messages->data));
    }

    public function testAdminCanSaveQaTicketAsFaqValid()
    {
        $ticket = Ticket::factory()->withCategoryId(TicketCategoryData::LESSON_QA)->withTicketStatus(TicketStatusData::RESOLVED)->withAdmin($this->admin)->withUser($this->user)->create();
        TicketMessage::factory()->withUserId($this->user->id)->withTicketId($ticket->id)->create();
        TicketMessage::factory()->withAdminId($this->admin->id)->withTicketId($ticket->id)->withMessage('admin message')->create();

        DB::table('lesson_ticket')->insert(
            [
                'lesson_id' => $this->course->lesson->id,
                'ticket_id' => $ticket->id,
            ]
        );

        $response = $this->json('POST', 'api/af/tickets/'.$ticket->id.'/qa');

        $response->assertOk();
    }

    public function testAdminCanSaveQaTicketAsFaqInValid()
    {
        $ticket = Ticket::factory()->withCategoryId(TicketCategoryData::CONTENT)->create();
        $response = $this->json('POST', 'api/af/tickets/'.$ticket->id.'/qa');

        $response->assertStatus(400);
    }
}
