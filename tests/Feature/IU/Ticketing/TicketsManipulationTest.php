<?php

namespace Tests\Feature\IU\Ticketing;

use App\Models\TicketSubject;
use App\Models\User;
use Tests\TestCase;

class TicketsManipulationTest extends TestCase
{
    private $user;

    private $ticketSubjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
        $this->ticketSubjects = TicketSubject::factory(5)->create();
    }

    public function testTicketsSubjectsGetRouteValid()
    {
        $response = $this->json('GET', 'api/iu/tickets/subjects');

        $response->assertStatus(200);

        $this->assertEquals(count($this->ticketSubjects), count(json_decode($response->content())));
    }

    public function testTicketsSubjectsGetRouteByIdValid()
    {
        $response = $this->json('GET', '/api/iu/tickets/subjects/'.$this->ticketSubjects[0]->id);

        $response->assertStatus(200);

        $this->assertEquals($this->ticketSubjects[0]->id, json_decode($response->content())->id);
    }
}
