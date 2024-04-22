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
     *
     * @var $userId
     * @var $description
     */
    public $userId;
    public $description;
    public $ticketId;

    /**
     * Create a new event instance.
     *
     * @param $ticketId
     * @param $userId
     * @param $description
     */
    public function __construct($ticketId, $userId, $description)
    {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->description = $description;
    }
}
