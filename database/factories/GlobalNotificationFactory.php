<?php

namespace Database\Factories;

use App\Models\GlobalNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GlobalNotificationFactory extends Factory
{
    /**
     * The name of the global notification's corresponding model.
     *
     * @var string
     */
    protected $model = GlobalNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user_id = DB::table('users')->pluck('id');
        return [
            'user_id'       =>  $user_id->random(),
            'title'         =>  Str::random(10),
            'description'   =>  fake()->sentence,
            'body'          =>  fake()->randomHtml(2, 3),
            'archive_at'    =>  fake()->dateTimeBetween('now', '+01 days'),
            'show_modal'    =>  0
        ];
    }

    public function withUserId($id)
    {
        return $this->state(fn () => [
            'user_id'   =>  $id,
        ]);
    }

    public function archived()
    {
        return $this->state(fn () => [
            'is_archived'   =>  1,
        ]);
    }
}
