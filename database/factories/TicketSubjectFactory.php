<?php

namespace Database\Factories;

use App\Models\TicketSubject;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketSubjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TicketSubject::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        return [
            'ticket_category_id'    =>  1,
            'name'                  =>  fake()->unique()->city(),
            'desc'                  =>  'test desc',
            'only_logged_in_users'  =>  1,
        ];
    }
    public function withName($name)
    {
        return $this->state(fn () => [
            'name'  =>  $name,
        ]);
    }

    public function withId($id)
    {
        return $this->state(fn () => [
            'ticket_category_id'    =>   $id,
        ]);
    }

    public function guest()
    {
        return $this->state(fn () => [
            'only_logged_in_users'  =>  0,
        ]);
    }
}
