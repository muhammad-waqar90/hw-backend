<?php

namespace Tests\Feature\IU\GDPR;

use App\DataObject\GDPRStatusData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GdprTest extends TestCase
{
    private $user;
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
    }

    public function testGdprGetRoute()
    {
        $uuid = '957d00dc-82cf-47d3-8d65-0f76df909ad9';

        DB::table('user_gdpr_requests')->insert([
            'user_id' => $this->user->id,
            'uuid' => $uuid,
            'status' => GDPRStatusData::READY,
            'downloaded' => 0,

        ]);
        $response = $this->json('GET', '/api/gdpr/user/'.$uuid);

        $response->assertStatus(200);
    }
}
