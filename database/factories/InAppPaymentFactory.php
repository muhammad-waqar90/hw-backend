<?php

namespace Database\Factories;

use App\Models\InAppPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InAppPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'transaction_id' => fake()->numberBetween(1000, 100000),
            'transaction_receipt' => Str::random(30),
        ];
    }
}
