<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => fake()->unique()->city(),
            'display_name' => Str::random(10),
            'description' => Str::random(10),
            'related_permissions' => '',
        ];
    }

    public function withName($name)
    {
        return $this->state(fn () => [
            'name' => $name,
        ]);
    }

    public function withDisplayName($displayName)
    {
        return $this->state(fn () => [
            'display_name' => $displayName,
        ]);
    }
}
