<?php

namespace Database\Factories;

use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\Models\PurchaseHistory;
use App\Models\StripePayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class PurchaseHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $stripe_payment = StripePayment::factory()->create();
        $user_id = DB::table('users')->pluck('id');

        return [
            'user_id' => $user_id->random(),
            'entity_id' => $stripe_payment->id,
            'entity_type' => PurchaseHistoryEntityData::ENTITY_STRIPE_PAYMENT,
            'amount' => fake()->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 800),
        ];
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id' => $id,
        ]);
    }

    public function withEntityId($id)
    {
        return $this->state(fn () => [
            'entity_id' => $id,
        ]);
    }

    public function withEntityType($type)
    {
        return $this->state(fn () => [
            'entity_type' => $type,
        ]);
    }
}
