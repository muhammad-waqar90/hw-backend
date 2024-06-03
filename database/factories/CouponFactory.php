<?php

namespace Database\Factories;

use App\DataObject\CouponData;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => Str::random(10),
            'description' => fake()->sentence,
            'code' => Str::random(10),
            'value_type' => CouponData::PERCENTAGE,
            'value' => fake()->randomNumber(2),
            'status' => CouponData::ACTIVE,
            'redeem_limit' => fake()->randomNumber(2),
            'redeem_limit_per_user' => 1,
        ];
    }

    public function withStatus($status)
    {
        return $this->state(fn () => [
            'course_status' => $status,
        ]);
    }
}
