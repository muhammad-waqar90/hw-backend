<?php

namespace Database\Factories;

use App\DataObject\AF\EventTypeData;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => Str::random(10),
            'description' => Str::random(20),
            'type' => EventTypeData::NATIONAL,
            'url' => fake()->url,
            'start_date' => fake()->dateTimeBetween('now', '+10 days'),
            'end_date' => fake()->dateTimeBetween('now', '+15 days'),
        ];
    }

    public function withType($type)
    {
        return $this->state(fn () => [
            'type' => $type,
        ]);
    }
}
