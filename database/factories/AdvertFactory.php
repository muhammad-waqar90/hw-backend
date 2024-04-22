<?php

namespace Database\Factories;

use App\DataObject\AdvertData;
use App\Models\Advert;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdvertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Advert::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        return [
            'name'          =>  Str::random(10),
            'url'           =>  fake()->url,
            'img'           =>  fake()->imageUrl,
            'priority'      =>  AdvertData::DEFAULT_PRIORITY,
            'expires_at'    =>  fake()->dateTimeBetween('now', '+01 days'),
            'status'        =>  AdvertData::STATUS_ACTIVE,
        ];
    }

    public function inactive()
    {
        return $this->state(fn () => [
            'status'    =>  AdvertData::STATUS_INACTIVE,
        ]);
    }
}
