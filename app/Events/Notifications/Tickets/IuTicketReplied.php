<?php

namespace App\Events\Notifications\Tickets;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IuTicketReplied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * IU ticket reply
     */
    public $userId;

    public $description;

    public $ticketId;

    /**
     * Create a new event instance.
     */
    public function __construct($ticketId, $userId, $description)
    {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->description = $description;
    }
}
