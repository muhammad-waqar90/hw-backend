<?php

namespace Database\Factories;

use App\DataObject\GuestData;
use App\Models\GuestCart;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GuestCartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GuestCart::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'cart_id'       =>  Str::random(5),
            'items'         =>  null,
            'is_completed'  =>  GuestData::GUEST_CART_INCOMPLETE
        ];
    }

    public function withCartId($cart_id)
    {
        return $this->state(fn () => [
            'cart_id'   =>  $cart_id
        ]);
    }

    public function withItem($items)
    {
        return $this->state(fn () => [
            'items'   =>  $items
        ]);
    }
}
