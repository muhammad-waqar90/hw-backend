<?php

namespace Tests\Feature\MA;

use App\Models\User;

use Tests\TestCase;

class MasterAdminManipulationTest extends TestCase
{
    private $wrongUser;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $user = User::factory()->verified()->mAdmin()->create();
        $this->wrongUser = User::factory()->verified()->create();
        $this->actingAs($user);
    }

    public function testDefaultHeadAdminGetRouteValid()
    {
        $response = $this->json('GET',  '/api/ma/ha');

        $response->assertStatus(200);
    }

    public function testHeadAdminGetRouteValid()
    {
        User::factory(5)->verified()->hAdmin()->create();

        $response = $this->json('GET',  '/api/ma/ha');
        
        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testHeadAdminGetRouteInvalid()
    {
        User::factory(5)->verified()->hAdmin()->create();

        $response = $this->actingAs($this->wrongUser)->json('GET',  '/api/ma/ha');
        
        $response->assertStatus(403);
    }

    public function testHeadAdminDeleteRouteValid()
    {
        $user = User::factory(5)->verified()->hAdmin()->create();

        $response = $this->json('DELETE',  '/api/ma/ha/'.$user[0]->id);
        
        $response->assertStatus(200);
    }

    public function testHeadAdminDeleteRouteInvalid()
    {
        $user = User::factory()->verified()->hAdmin()->create();

        $response = $this->actingAs($this->wrongUser)->json('DELETE',  '/api/ma/ha/'.$user->id);
        
        $response->assertStatus(403);
    }
}