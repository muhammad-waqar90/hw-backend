<?php

namespace Database\Factories;

use App\Models\StripePayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StripePaymentFactory extends Factory
{
    /**
     * The name of the StripePayment's corresponding model.
     *
     * @var string
     */
    protected $model = StripePayment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'stripe_id' => Str::random(30),
            'stripe_object' => Str::random(10),
        ];
    }
}
