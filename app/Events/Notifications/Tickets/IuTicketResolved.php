<?php

namespace App\Events\Notifications\Tickets;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IuTicketResolved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * IU ticket resolved
     */
    public $userId;

    public $userName;

    public $ticketId;

    /**
     * Create a new event instance.
     */
    public function __construct($ticketId, $userId, $userName)
    {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->userName = $userName;
    }
}
