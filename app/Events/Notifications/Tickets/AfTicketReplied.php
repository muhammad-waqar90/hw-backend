<?php

namespace App\Events\Notifications\Tickets;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AfTicketReplied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * AF ticket reply
     */
    public $userId;

    public $description;

    public $subject;

    public $ticketId;

    public $iuTicketLinkIds;

    /**
     * Create a new event instance.
     */
    public function __construct($ticketId, $userId, $description, $subject, $iuTicketLinkIds = null)
    {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->description = $description;
        $this->subject = $subject;
        $this->iuTicketLinkIds = $iuTicketLinkIds;
    }
}
