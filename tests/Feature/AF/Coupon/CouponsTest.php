<?php

namespace Tests\Feature\AF\Coupon;

use App\DataObject\CouponData;
use App\Models\Coupon;
use App\Models\User;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Support\Str;
use Tests\TestCase;

class CouponsTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $user;

    private $admin;

    private $wrongAdmin;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->admin = User::factory()->verified()->admin()->create();
        $this->wrongAdmin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
    }

    public function testCouponsDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/af/coupons');

        $response->assertStatus(200);
    }

    public function testCouponsGetRoute()
    {
        Coupon::factory(5)->create();
        $response = $this->json('GET', '/api/af/coupons');

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testCouponsGetByIdRoute()
    {
        $coupon = Coupon::factory()->create();
        $response = $this->json('GET', '/api/af/coupons/'.$coupon->id);

        $response->assertStatus(200);
    }

    public function testCouponsPostRoute()
    {
        $response = $this->json('POST', '/api/af/coupons', [
            'name' => Str::random(10),
            'description' => Str::random(50),
            'code' => Str::random(10),
            'value' => 10,
            'value_type' => CouponData::PERCENTAGE,
            'status' => CouponData::ACTIVE,
            'redeem_limit' => 1,
            'redeem_limit_per_user' => 1,
            'individual_use' => 0,
            'restrictions' => [
                [
                    'id' => [116],
                    'type' => 'course',
                ],
            ],
        ]);

        $response->assertStatus(200);
    }

    public function testCouponsPutRoute()
    {
        $coupon = Coupon::factory()->create();
        $response = $this->json('PUT', '/api/af/coupons/'.$coupon->id, [
            'name' => Str::random(10),
            'description' => Str::random(50),
            'status' => CouponData::ACTIVE,
            'redeem_limit' => 1,
            'redeem_limit_per_user' => 1,
        ]);

        $response->assertStatus(200);
    }
}
