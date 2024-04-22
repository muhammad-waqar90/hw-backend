<?php

namespace Tests\Feature\IU\Coupon;

use App\DataObject\AF\CourseStatusData;
use App\DataObject\CouponData;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CouponsTest extends TestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testCouponsCanRedeem()
    {
        $coupon = Coupon::factory()->create();
        $category = Category::factory()->create();
        $course = Course::factory()->withId($category->id)->withStatus(CourseStatusData::PUBLISHED)->create();
        DB::table('coupon_restrictions')->insert(
            [
                'coupon_id'     =>  $coupon->id,
                'entity_id'     =>  $course->id,
                'entity_type'   =>  CouponData::ENTITY_MODEL['course']
            ]
        );

        $response = $this->json('POST',  '/api/iu/coupons/redeem/can', [
            "code"  => $coupon->code,
            "cart"  => [
                "course"    => [$course->id]
            ]
        ]);

        $response->assertStatus(200);
    }
}