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
     *
     * @var $userId
     * @var $userName
     */
    public $userId;
    public $userName;
    public $ticketId;

    /**
     * Create a new event instance.
     *
     * @param $ticketId
     * @param $userId
     * @param $userName
     */
    public function __construct($ticketId, $userId, $userName)
    {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->userName = $userName;
    }
}
