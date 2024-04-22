<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        $ticket_category_id = DB::table('ticket_categories')->pluck('id');
        $admin_id = DB::table('users')->pluck('id');
        $log_test = array('previousRoute' => array('url1', 'url2'));

        return [
            'ticket_category_id'    =>  $ticket_category_id->random(),
            'ticket_status_id'      =>  fake()->numberBetween($min = 1, $max = 4),
            'user_id'               =>  null,
            'admin_id'              =>  null,
            'user_email'            =>  'guestTest@test.com',
            'subject'               =>  'some subject',
            'log'                   =>  json_encode($log_test)
        ];
    }

    public function withCategoryId($id)
    {
        return $this->state(fn () => [
            'ticket_category_id'    =>  $id,
        ]);
    }

    public function withTicketStatus($status)
    {
        return $this->state(fn () => [
            'ticket_status_id'  =>  $status,
        ]);
    }

    public function withUser($user)
    {
        return $this->state(fn () => [
            'user_id'       =>  $user->id,
            'user_email'    =>  $user->email,
        ]);
    }

    public function withEmail($email)
    {
        return $this->state(fn () => [
            'user_email'    =>  $email,
        ]);
    }

    public function withAdmin($admin)
    {
        return $this->state(fn () => [
            'admin_id'  =>  $admin->id,
        ]);
    }
}
