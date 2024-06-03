<?php

namespace Database\Factories;

use App\DataObject\RoleData;
use App\DataObject\Tests\UserData;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => RoleData::INDEPENDENT_USER,
            'name' => Str::random(2).'_'.Str::random(8),
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'email_verified_at' => null,
            'password' => static::$password ??= Hash::make(UserData::PASSWORD),
            'remember_token' => null,
            'is_enabled' => 1,
        ];
    }

    /**
     * Indicate that the user is verified.
     */
    public function verified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => now(),
        ]);
    }

    public function withName($name)
    {
        return $this->state(function () use ($name) {
            return [
                'name' => $name,
            ];
        });
    }

    public function institutional()
    {
        return $this->state(fn () => [
            'role_id' => RoleData::INSTITUTIONAL_USER,
        ]);
    }

    public function admin()
    {
        return $this->state(fn () => [
            'role_id' => RoleData::ADMIN,
        ]);
    }

    public function deactivated()
    {
        return $this->state(fn () => [
            'is_enabled' => 0,
        ]);
    }

    public function hAdmin()
    {
        return $this->state(fn () => [
            'role_id' => RoleData::HEAD_ADMIN,
        ]);
    }

    public function mAdmin()
    {
        return $this->state(fn () => [
            'role_id' => RoleData::MASTER_ADMIN,
        ]);
    }

    public function disabled()
    {
        return $this->state(fn () => [
            'is_enabled' => 0,
        ]);
    }
}
