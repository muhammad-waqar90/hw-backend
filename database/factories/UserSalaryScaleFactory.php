<?php

namespace Database\Factories;

use App\Models\DiscountedCountry;
use App\Models\DiscountedCountryRange;
use App\Models\User;
use App\Models\UserSalaryScale;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSalaryScaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->verified()->create();
        $discountedCountry = DiscountedCountry::query()->first();
        $discountedCountryRange = DiscountedCountryRange::query()->first();

        return [
            'user_id' => $user->id,
            'discounted_country_id' => $discountedCountry->id,
            'discounted_country_range_id' => $discountedCountryRange->id,
            'declaration' => 1,
        ];
    }

    public function withUserId($userId)
    {
        return $this->state(fn () => [
            'user_id' => $userId,
        ]);
    }
}
