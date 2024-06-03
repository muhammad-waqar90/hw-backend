<?php

namespace Database\Factories;

use App\Models\PermGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PermGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'permTestGroup_'.Str::random(5),
            'description' => Str::random(10),
        ];
    }

    public function withName($name)
    {
        return $this->state(fn () => [
            'name' => $name,
        ]);
    }
}
