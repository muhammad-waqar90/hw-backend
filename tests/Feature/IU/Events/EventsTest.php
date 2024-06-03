<?php

namespace Tests\Feature\IU\Events;

use App\DataObject\AF\EventTypeData;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

class EventsTest extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testEventsDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/iu/events', [
            'from' => date('Y-m-d'),
            'to' => date('Y-m-d', strtotime('+1 months')),
        ]);

        $response->assertStatus(200);
    }

    public function testEventsGetRoute()
    {
        Event::factory(5)->create();
        $response = $this->json('GET', '/api/iu/events', [
            'from' => date('Y-m-d'),
            'to' => date('Y-m-d', strtotime('+1 months')),
        ]);

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())));
    }

    public function testEventGetByIdRoute()
    {
        $event = Event::factory()->create();
        $response = $this->json('GET', '/api/iu/events/'.$event->id);

        $response->assertStatus(200);
        $this->assertGreaterThan(1, json_decode($response['id']));
    }

    public function testEventType()
    {
        $globalEvent = Event::factory()->withType(EventTypeData::GLOBAL)->create();
        $nationalEvent = Event::factory()->withType(EventTypeData::NATIONAL)->create();

        $response = $this->json('GET', '/api/iu/events/'.$globalEvent->id);

        $response->assertStatus(200);
        $this->assertEquals(EventTypeData::GLOBAL, json_decode($response['type']));

        $response = $this->json('GET', '/api/iu/events/'.$nationalEvent->id);

        $response->assertStatus(200);
        $this->assertEquals(EventTypeData::NATIONAL, json_decode($response['type']));
    }
}
