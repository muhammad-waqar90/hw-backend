<?php

namespace Database\Factories;

use App\DataObject\Notifications\NotificationTypeData;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
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
            'title' => Str::random(10),
            'description' => fake()->sentence,
            'type' => NotificationTypeData::SUPPORT_TICKET,
            'read' => 0,
        ];
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id' => $id,
        ]);
    }

    public function withType($type)
    {
        return $this->state(fn () => [
            'type' => $type,
        ]);
    }

    public function read()
    {
        return $this->state(fn () => [
            'read' => 1,
        ]);
    }
}
