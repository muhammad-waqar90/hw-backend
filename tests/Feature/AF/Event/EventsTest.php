<?php

namespace Tests\Feature\AF\Event;

use App\DataObject\AF\EventTypeData;
use App\Models\Event;
use App\Models\User;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventsTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
    }

    public function testEventsDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/af/events');

        $response->assertStatus(200);
    }

    public function testEventsGetRoute()
    {
        Event::factory(5)->create();
        $response = $this->json('GET', '/api/af/events');

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testEventsTypeFilterGetRoute()
    {
        Event::factory(5)->withType(EventTypeData::GLOBAL)->create();
        Event::factory(5)->withType(EventTypeData::NATIONAL)->create();

        $response = $this->json('GET', '/api/af/events?type='.EventTypeData::GLOBAL);

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testEventsGetByIdRoute()
    {
        $event = Event::factory()->create();
        $response = $this->json('GET', '/api/af/events/'.$event->id);

        $response->assertStatus(200);
        $this->assertEquals($event->id, json_decode($response->content())->id);
    }

    public function testEventsPostRoute()
    {
        $title = Str::random(10);
        $response = $this->json('POST', '/api/af/events', [
            'title' => $title,
            'description' => Str::random(20),
            'type' => EventTypeData::GLOBAL,
            'url' => 'https://google.com',
            'start_date' => date('Y-m-d\\TH:i', strtotime('+1 years')),
            'end_date' => date('Y-m-d\\TH:i', strtotime('+1 years')),
        ]);

        $response->assertStatus(200);

        //Get created event by title text
        $response = $this->json('GET', '/api/af/events?searchText='.$title);

        $response->assertStatus(200);
        $this->assertEquals(1, count(json_decode($response->content())->data));
    }

    public function testEventsUpdateRoute()
    {
        $event = Event::factory()->create();

        $response = $this->json('POST', '/api/af/events/'.$event->id, [
            'title' => Str::random(10),
            'description' => Str::random(20),
            'type' => EventTypeData::GLOBAL,
            'url' => 'https://google.com',
            'start_date' => date('Y-m-d\\TH:i', strtotime('+1 years')),
            'end_date' => date('Y-m-d\\TH:i', strtotime('+1 years')),
        ]);

        $response->assertStatus(200);

        //get updated event
        $response = $this->json('GET', '/api/af/events/'.$event->id);

        $response->assertStatus(200);
        $this->assertEquals(EventTypeData::GLOBAL, json_decode($response->content())->type);
    }

    public function testEventsDeleteRoute()
    {
        $event = Event::factory()->create();
        $response = $this->json('Delete', '/api/af/events/'.$event->id);

        $response->assertStatus(200);

        //get deleted event
        $response = $this->json('GET', '/api/af/events/'.$event->id);

        $response->assertStatus(404);
    }
}
