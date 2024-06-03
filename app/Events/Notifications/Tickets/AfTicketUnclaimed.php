<?php

namespace App\Events\Notifications\Tickets;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AfTicketUnclaimed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * AF claimed ticket
     */
    public $userId;

    public $subject;

    public $userName;

    public $message;

    public $ticketId;

    /**
     * Create a new event instance.
     */
    public function __construct($ticketId, $userId, $subject, $userName, $message)
    {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->subject = $subject;
        $this->userName = $userName;
        $this->message = $message;
    }
}
