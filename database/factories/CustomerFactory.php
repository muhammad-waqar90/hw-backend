<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user_id = DB::table('users')->pluck('id');

        return [
            'user_id' => $user_id->random(),
            'email' => fake()->email,
            'stripe_id' => 'cus_JTeINfrZ8rXYJg',
            'pm_type' => 'visa',
            'pm_last_four' => '4242',
        ];
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id' => $id,
        ]);
    }
}
