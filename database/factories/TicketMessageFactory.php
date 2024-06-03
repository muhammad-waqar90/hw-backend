<?php

namespace Database\Factories;

use App\DataObject\Tickets\TicketMessageTypeData;
use App\Models\TicketMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TicketMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $ticket_id = DB::table('tickets')->pluck('id');

        return [
            'user_id' => null,
            'ticket_id' => $ticket_id->random(),
            'message' => 'test message',
            'type' => TicketMessageTypeData::USER_MESSAGE,
        ];
    }

    public function withUserId($user_id)
    {
        return $this->state(fn () => [
            'user_id' => $user_id,
        ]);
    }

    public function withAdminId($admin_id)
    {
        return $this->state(fn () => [
            'user_id' => $admin_id,
            'type' => TicketMessageTypeData::ADMIN_MESSAGE,
        ]);
    }

    public function withSystemMessage()
    {
        return $this->state(fn () => [
            'type' => TicketMessageTypeData::SYSTEM_MESSAGE,
        ]);
    }

    public function withAdminOnlySystemMessage()
    {
        return $this->state(fn () => [
            'type' => TicketMessageTypeData::ADMIN_ONLY_SYSTEM_MESSAGE,
        ]);
    }

    public function withTicketId($id)
    {
        return $this->state(fn () => [
            'ticket_id' => $id,
        ]);
    }

    public function withMessage($message)
    {
        return $this->state(fn () => [
            'message' => $message,
        ]);
    }
}
