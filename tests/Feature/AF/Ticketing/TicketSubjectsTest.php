<?php

namespace Tests\Feature\AF\Ticketing;

use App\Models\User;
use App\Models\TicketSubject;

use Illuminate\Support\Facades\DB;

use Tests\TestCase;

use App\Traits\Tests\PermGroupUserTestTrait;

class TicketSubjectsTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $user, $wrongUser, $tickets;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->admin()->create();
        $this->wrongUser = User::factory()->admin()->verified()->create();
        $this->actingAs($this->user);
        $this->assignAllPermissionToUser($this->user);
        $this->tickets = TicketSubject::factory(5)->create();
    }

    public function testTicketsCategoriesGetRouteValid()
    {
        $response = $this->json('GET',  'api/af/tickets/categories');

        $response->assertStatus(200);
    }

    public function testNoPermTicketsCategoriesGetRouteValid()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET',  'api/af/tickets/categories');

        $response->assertStatus(200);
    }

    public function testTicketsSubjectsGetRouteValid()
    {
        $response = $this->json('GET',  'api/af/tickets/subjects');

        $response->assertStatus(200);

        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testNoPermsTicketsSubjectsGetRouteInvalid()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET',  'api/af/tickets/subjects');

        $response->assertStatus(403);
    }

    public function testTicketsSubjectsPostRouteValid()
    {
        $response = $this->json('POST',  'api/af/tickets/subjects', array('categoryId' => '1', 'name' => 'Subject System 1', 'desc' => 'desc', 'only_logged_in' => '1'));

        $response->assertStatus(200);
    }

    public function testTicketsSubjectsForGuestsPostRouteValid()
    {
        $response = $this->json('POST',  'api/af/tickets/subjects', array('categoryId' => '1', 'name' => 'Subject System 1', 'desc' => 'desc', 'only_logged_in' => '0'));

        $response->assertStatus(200);
    }

    public function testTicketsSubjectsPostRouteInvalid()
    {
        $response = $this->json('POST',  'api/af/tickets/subjects', array('categoryId' => '1', 'name' => 'Subject System 1', 'desc' => 'desc', 'only_logged_in' => '5'));

        $response->assertStatus(422);
    }

    public function testTicketsSubjectsGetRouteValidSearch()
    {
        $response = $this->json('GET',  'api/af/tickets/subjects?searchText=' . $this->tickets[0]->name);

        $response->assertStatus(200);

        $this->assertEquals(1, count(json_decode($response->content())->data));
    }

    public function testTicketsSubjectsGetRouteByIdValid()
    {
        $response = $this->json('GET',  'api/af/tickets/subjects/' . $this->tickets[0]->id);

        $response->assertStatus(200);

        $this->assertEquals($this->tickets[0]->id, json_decode($response->content())->id);
    }

    public function testTicketsSubjectsPutRouteValid()
    {
        $response = $this->json('PUT',  'api/af/tickets/subjects/' . $this->tickets[0]->id, array('categoryId' => '1', 'name' => 'Test Subject', 'only_logged_in' => '1'));

        $response->assertStatus(200);

        $this->assertEquals('Test Subject', DB::table('ticket_subjects')->where('id', $this->tickets[0]->id)->first()->name);
    }

    public function testTicketsSubjectsPutRouteInvalidUnprocessable()
    {
        $response = $this->json('PUT',  'api/af/tickets/subjects/' . $this->tickets[0]->id, array('categoryId' => '1', 'name' => 'Test Subject', 'desc' => 'desc', 'only_logged_in' => '5'));

        $response->assertStatus(422);
    }

    public function testTicketsSubjectsDelRouteValid()
    {
        $response = $this->json('DELETE',  'api/af/tickets/subjects/' . $this->tickets[0]->id);

        $response->assertStatus(200);

        $this->assertEquals(4, DB::table('ticket_subjects')->count());
    }
}
