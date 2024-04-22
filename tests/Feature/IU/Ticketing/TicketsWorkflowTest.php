<?php

namespace Tests\Feature\IU\Ticketing;

use App\DataObject\Tickets\TicketCategoryData;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketSubject;

use App\DataObject\Tickets\TicketStatusData;
use App\Models\TicketMessage;
use App\Models\UserProfile;
use Tests\TestCase;

class TicketsWorkflowTest extends TestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        UserProfile::factory()->withUser($this->user->id)->create();
        $this->actingAs($this->user);
    }

    public function testTicketPostRouteValid(){
        $ticketSubject = TicketSubject::factory()->create();

        $response = $this->json('POST',  '/api/iu/tickets',[
            "subjectId" => $ticketSubject->id,
            "message" => "I as an IU have an issue",
            "assets" => [],
            "log" => []
        ]);

        $response->assertStatus(200);
    }

    public function testTicketGetRouteByIdValid(){
        $tickets = Ticket::factory(5)->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        $response = $this->json('GET',  '/api/iu/tickets/' . $tickets[0]->id);

        $response->assertStatus(200);
        $this->assertEquals($tickets[0]->id , json_decode($response->content())->ticket->id);
    }

    public function testTicketGetRouteDefaultValid(){
        $response = $this->json('GET',  '/api/iu/tickets/me');

        $response->assertStatus(200);
        $this->assertEquals(0 , count(json_decode($response->content())->data));
    }

    public function testTicketGetRouteValid(){
        $unclaimedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $inprogressTicket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $resolvedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $reopenedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::REOPENED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        TicketMessage::factory()->withTicketId($unclaimedTicket->id)->create();
        TicketMessage::factory()->withTicketId($inprogressTicket->id)->create();
        TicketMessage::factory()->withTicketId($resolvedTicket->id)->create();
        TicketMessage::factory()->withTicketId($reopenedTicket->id)->create();

        $response = $this->json('GET',  '/api/iu/tickets/me');

        $response->assertStatus(200);
        $this->assertEquals(4 , count(json_decode($response->content())->data));
    }

    public function testTicketGetRouteByStatusValid(){
        $unclaimedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $inprogressTicket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $resolvedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $reopenedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::REOPENED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        TicketMessage::factory()->withTicketId($unclaimedTicket->id)->create();
        TicketMessage::factory()->withTicketId($inprogressTicket->id)->create();
        TicketMessage::factory()->withTicketId($resolvedTicket->id)->create();
        TicketMessage::factory()->withTicketId($reopenedTicket->id)->create();

        $response = $this->json('GET',  '/api/iu/tickets/me?status='.TicketStatusData::UNCLAIMED);

        $response->assertStatus(200);

        //2 unclaimed and one reopened ticket
        $this->assertEquals(2 , count(json_decode($response->content())->data));
    }

    public function testTicketGetRouteBySubjectValid(){
        $unclaimedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $inprogressTicket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $resolvedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $reopenedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::REOPENED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        TicketMessage::factory()->withTicketId($unclaimedTicket->id)->create();
        TicketMessage::factory()->withTicketId($inprogressTicket->id)->create();
        TicketMessage::factory()->withTicketId($resolvedTicket->id)->create();
        TicketMessage::factory()->withTicketId($reopenedTicket->id)->create();

        $response = $this->json('GET',  '/api/iu/tickets/me?subject='."some subject");

        $response->assertStatus(200);
        $this->assertEquals(4 , count(json_decode($response->content())->data));
    }

    public function testTicketGetRouteByStatusAndSubjectValid(){
        $unclaimedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $inprogressTicket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $resolvedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();
        $reopenedTicket = Ticket::factory()->withTicketStatus(TicketStatusData::REOPENED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        TicketMessage::factory()->withTicketId($unclaimedTicket->id)->create();
        TicketMessage::factory()->withTicketId($inprogressTicket->id)->create();
        TicketMessage::factory()->withTicketId($resolvedTicket->id)->create();
        TicketMessage::factory()->withTicketId($reopenedTicket->id)->create();

        $response = $this->json('GET',  '/api/iu/tickets/me?status='.TicketStatusData::RESOLVED."&subject=some subject");

        $response->assertStatus(200);
        $this->assertEquals(1 , count(json_decode($response->content())->data));
    }

    public function testTicketMessagePostRouteValid(){
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::UNCLAIMED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        $response = $this->json('POST',  '/api/iu/tickets/'.$ticket->id.'/messages',[
            "message" => "New message",
            "assets" => []
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1 , count(json_decode($response->content())->data->message));
    }

    public function testMarkTicketClosedValid(){
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::IN_PROGRESS)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        $response = $this->json('PUT',  '/api/iu/tickets/'.$ticket->id."/resolve");

        $response->assertStatus(200);
        $this->assertNotNull(json_decode($response->content())->data);
    }

    public function testMarkTicketReopenedValid(){
        $ticket = Ticket::factory()->withTicketStatus(TicketStatusData::RESOLVED)->withCategoryId(TicketCategoryData::SYSTEM)->withUser($this->user)->create();

        $response = $this->json('PUT',  '/api/iu/tickets/'.$ticket->id."/reopen");

        $response->assertStatus(200);
        $this->assertNotNull(json_decode($response->content())->data);
    }

}