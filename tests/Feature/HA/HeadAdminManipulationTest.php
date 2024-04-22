<?php

namespace Tests\Feature\HA;

use App\Models\User;
use App\Models\Ticket;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

use Tests\TestCase;

use App\Traits\Tests\PermGroupUserTestTrait;

class HeadAdminManipulationTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $wrongUser, $deactivatedAdmin, $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $user = User::factory()->verified()->hAdmin()->create();
        $this->wrongUser = User::factory()->verified()->create();
        $this->deactivatedAdmin = User::factory()->verified()->admin()->deactivated()->create();
        $this->actingAs($user);
        $this->data = $this->permissionsSeeder();
    }

    public function testHeadAdminGetRouteValidPage1()
    {
        $response = $this->json('GET',  '/api/ha/admins/');

        $response->assertStatus(200);

        $this->assertEquals(16, count(json_decode($response->content())->data));
    }

    public function testHeadAdminGetRouteValidPage2()
    {
        User::factory(10)->verified()->admin()->create();

        $response = $this->json('GET',  '/api/ha/admins/?page=2');

        $response->assertStatus(200);

        $this->assertEquals(6, count(json_decode($response->content())->data));
    }

    public function testHeadAdminGetRouteInvalidUser()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET',  '/api/ha/admins/');

        $response->assertStatus(403);
    }

    public function testHeadAdminGetRouteValidSingleUser()
    {
        $response = $this->json('GET',  '/api/ha/admins/' . $this->data->users[0]->id);

        $response->assertStatus(200);

        $this->assertEquals($this->data->users[0]->id, json_decode($response->content())->id);
    }

    public function testHeadAdminGetRouteValidSearch()
    {
        $response = $this->json('GET',  '/api/ha/admins?searchText=' . $this->data->users[0]->name);

        $response->assertStatus(200);

        $this->assertEquals(1, count(json_decode($response->content())->data));
    }

    public function testHeadAdminGetRouteValidSearch2()
    {
        User::factory()->verified()->admin()->withName('Admin1')->create();
        User::factory()->verified()->admin()->withName('Admin2')->create();
        $response = $this->json('GET',  '/api/ha/admins?searchText=Admin');

        $response->assertStatus(200);

        $this->assertEquals(2, count(json_decode($response->content())->data));
    }

    public function testHeadAdminGetRouteValidSearchNoResults()
    {
        $response = $this->json('GET',  '/api/ha/admins?searchText=adminWrongDoesntExists');

        $response->assertStatus(200);

        $this->assertEquals(0, count(json_decode($response->content())->data));
    }

    public function testHeadAdminGetRouteValidAll()
    {
        $response = $this->json('GET',  '/api/ha/admins/all');

        $response->assertStatus(200);

        $this->assertEquals(16, count(json_decode($response->content())));
    }

    public function testHeadAdminPostRouteValid()
    {
        $response = $this->json('POST', '/api/ha/admins/', array('email' => 'fakeEmail@fake.com', 'first_name' => 'Admin', 'last_name' => 'Normal', 'permGroupIds' => array($this->data->permGroups[0]->id, $this->data->permGroups[1]->id)));

        $response->assertStatus(200);
    }

    public function testHeadAdminPostRouteInvalidUserAlreadyExists()
    {
        $response = $this->json('POST', '/api/ha/admins/', array('email' => $this->data->users[0]->email, 'first_name' => 'Admin', 'last_name' => 'Normal', 'permGroupIds' => array($this->data->permGroups[0]->id, $this->data->permGroups[1]->id)));

        $response->assertStatus(422);
    }

    public function testHeadAdminPostNGetRouteValidUpdatedInGetRoute()
    {
        $response = $this->json('POST', '/api/ha/admins/', array('email' => 'fakeEmail@fake.com', 'first_name' => 'Admin', 'last_name' => 'Normal', 'permGroupIds' => array($this->data->permGroups[0]->id, $this->data->permGroups[1]->id)));

        $response = $this->json('GET',  '/api/ha/admins/');
        $this->assertEquals(17, count(json_decode($response->content())->data));
    }

    public function testHeadAdminDelRouteValid()
    {
        $response = $this->json('DELETE', '/api/ha/admins/' . $this->data->users[0]->id);

        $response->assertStatus(200);
    }

    public function testHeadAdminDelNGetRouteValidUpdatedInGetRoute()
    {
        $response = $this->json('DELETE', '/api/ha/admins/' . $this->data->users[0]->id);

        $response = $this->json('GET',  '/api/ha/admins/');
        $this->assertEquals(15, count(json_decode($response->content())->data));
    }

    public function testHeadAdminPutRouteValid()
    {
        $response = $this->json('PUT', '/api/ha/admins/' . $this->data->users[0]->id, array('permGroupIds' => array($this->data->permGroups[0]->id)));

        $response->assertStatus(200);
    }

    //ACTIVATE/DEACTIVATE//

    public function testHeadAdminDeactivateAdmin()
    {
        $response = $this->json('PUT', '/api/ha/admins/' . $this->data->users[0]->id . '/deactivate');

        $this->assertEquals('Successfully deactivated admin', json_decode($response->content())->message);
    }

    public function testHeadAdminActivateAdmin()
    {
        $response = $this->json('PUT', '/api/ha/admins/' . $this->deactivatedAdmin->id . '/activate');

        $this->assertEquals('Successfully activated admin', json_decode($response->content())->message);
    }

    public function testHeadAdminActivateActivatedAdmin()
    {
        $response = $this->json('PUT', '/api/ha/admins/' . $this->data->users[0]->id . '/activate');

        $this->assertEquals(Lang::get('general.notFound'), json_decode($response->content())->errors);
    }

    public function testUnassignDeactivatedAdminFromTicketWithStatusInProgress()
    {
        $ticket = Ticket::factory()->withAdmin($this->data->users[0])->withTicketStatus(2)->create();

        $this->json('PUT', '/api/ha/admins/' . $this->data->users[0]->id . '/deactivate');

        $this->assertEquals(null, DB::table('tickets')->where('id', $ticket->id)->pluck('admin_id')->first());
    }

    public function testUnassignDeletedAdminFromTicketWithStatusInProgress()
    {
        $ticket = Ticket::factory()->withAdmin($this->data->users[0])->withTicketStatus(2)->create();

        $this->json('DELETE', '/api/ha/admins/' . $this->data->users[0]->id);

        $this->assertEquals(null, DB::table('tickets')->where('id', $ticket->id)->pluck('admin_id')->first());
    }
}
