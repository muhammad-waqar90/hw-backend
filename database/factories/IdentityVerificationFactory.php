<?php

namespace Database\Factories;

use App\DataObject\IdentityVerificationStatusData;
use App\Models\IdentityVerification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class IdentityVerificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $users_id = DB::table('users')->pluck('id');

        return [
            'user_id' => $users_id->random(),
            'identity_file' => fake()->imageUrl(),
            'status' => IdentityVerificationStatusData::COMPLETED,
        ];
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withUser($userId)
    {
        return $this->state(fn () => [
            'user_id' => $userId,
        ]);
    }

    public function withStatus($status)
    {
        return $this->state(fn () => [
            'status' => $status,
        ]);
    }
}
