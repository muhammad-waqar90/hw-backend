<?php

namespace Database\Factories;

use App\Models\Refund;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class RefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $admin_id = DB::table('users')->pluck('id');
        $user_id = DB::table('users')->pluck('id');
        $purchase_item_id = DB::table('purchase_items')->pluck('id');

        return [
            'user_id' => $user_id->random(),
            'admin_id' => $admin_id->random(),
            'purchase_item_id' => $purchase_item_id->random(),
        ];
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id' => $id,
        ]);
    }

    public function withAdminId($id)
    {
        return $this->state(fn () => [
            'admin_id' => $id,
        ]);
    }

    public function withPurchaseItemId($id)
    {
        return $this->state(fn () => [
            'purchase_item_id' => $id,
        ]);
    }
}
