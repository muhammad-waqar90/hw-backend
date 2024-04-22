<?php

namespace Database\Factories;

use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TierFactory extends Factory
{
    /**
     * The name of the Tier's corresponding model.
     *
     * @var string
     */
    protected $model = Tier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'label' =>  Str::random(10),
            'value' =>  fake()->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 800),
        ];
    }
}
