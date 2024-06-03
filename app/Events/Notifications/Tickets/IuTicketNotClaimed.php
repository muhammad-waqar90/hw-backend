<?php

namespace App\Events\Notifications\Tickets;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IuTicketNotClaimed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * IU Ticket Not Claimed for more than 48 hours
     */
    public $ticketId;

    public $userId;

    public $subject;

    /**
     * Create a new event instance.
     */
    public function __construct($ticketId, $userId, $subject)
    {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->subject = $subject;
    }
}
