<?php

namespace Tests\Feature\GU\Ticketing;

use App\Models\User;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketSubject;

use Illuminate\Support\Facades\Lang;

use Tests\TestCase;

use App\Traits\Tests\PermGroupUserTestTrait;

class TicketsManipulationTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $user, $ticketSubjects, $ticketSubjectsGuest, $tickets, $messages;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->admin()->create();
        $this->tickets = Ticket::factory(5)->create();
        $this->ticketSubjects = TicketSubject::factory(5)->create();
        $this->ticketSubjectsGuest = TicketSubject::factory(2)->guest()->create();
        $this->messages = TicketMessage::factory(5)->create();
    }

    public function testGUTicketsSubjectsGetAllRouteValid()
    {
        $response = $this->json('GET',  'api/gu/tickets/subjects');

        $response->assertStatus(200);

        $this->assertEquals(2, count(json_decode($response->content())));
    }

    public function testGUTicketsSubjectsGetRouteInvalid()
    {
        $response = $this->json('GET',  'api/gu/tickets/subjects/' . $this->ticketSubjects[0]->id);

        $response->assertStatus(404);
    }

    public function testGUTicketsSubjectsGetSingleNotGuestRouteValid()
    {
        $response = $this->json('GET',  'api/gu/tickets/subjects/' . $this->ticketSubjectsGuest[0]->id);

        $response->assertStatus(200);
    }

    public function testGUTicketsSubjectsPostRouteValid()
    {
        $response = $this->json('POST',  'api/gu/tickets', array(
            'subjectId' => $this->ticketSubjectsGuest[0]->id,
            'message' => 'I haba issue boss',
            'assets' => [],
            'email' => 'gueest@email.com',
            'log' => array('url', 'url2')
        ));

        $response->assertStatus(200);

        $this->assertEquals(Lang::get('tickets.successfullySubmittedTicket'), json_decode($response->content())->message);
    }

    public function testGUTicketsSubjectsPostRouteInvalid()
    {
        $response = $this->json('POST',  'api/gu/tickets', array(
            'subjectId' => -5,
            'message' => 'I haba issue boss',
            'assets' => [],
            'email' => 'gueest@email.com',
            'log' => array('url', 'url2')
        ));

        $response->assertStatus(422);

        $this->assertEquals(Lang::get('tickets.invalidSubjectId'), json_decode($response->content())->errors);
    }

    public function testGUTicketsSubjectIdPostAlreadyExistsValid()
    {
        $response = $this->json('POST',  'api/gu/tickets', array(
            'subjectId' => $this->ticketSubjectsGuest[0]->id,
            'message' => 'I haba issue boss',
            'assets' => [],
            'email' => 'gueest@email.com',
            'log' => array('url', 'url2')
        ));

        $response->assertStatus(200);
    }
}
