<?php

namespace Tests\Feature\IU\Advert;

use App\Models\Advert;
use App\Models\User;
use Tests\TestCase;

class AdvertsTest extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testAdvertDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/iu/adverts');

        $response->assertStatus(200);
    }

    public function testAdvertGetRoute()
    {
        Advert::factory(5)->create();
        $response = $this->json('GET', '/api/iu/adverts');

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())));
    }

    public function testInActiveAdvertGetRoute()
    {
        Advert::factory(5)->inactive()->create();
        $response = $this->json('GET', '/api/iu/adverts');

        $response->assertStatus(200);
        $this->assertEquals(0, count(json_decode($response->content())));
    }
}
