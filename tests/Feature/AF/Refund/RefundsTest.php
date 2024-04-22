<?php

namespace Tests\Feature\AF\Refund;

use App\Models\User;

use App\Traits\Tests\PermGroupUserTestTrait;
use App\Traits\Tests\RefundTestTrait;

use Tests\TestCase;

class RefundsTest extends TestCase
{
    use PermGroupUserTestTrait;
    use RefundTestTrait;

    private $user, $admin, $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->admin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
        $this->data = $this->RefundSeeder($this->user);
    }

    public function testRefundsGetRoute(){
        $response = $this->json('GET',  '/api/af/refunds');

        $response->assertStatus(200);
    }
}