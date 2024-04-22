<?php

namespace App\Events\Notifications\Tickets;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AfTicketResolved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * AF ticket resolved
     *
     * @var $userId
     * @var $subject
     * @var $userName
     * @var $message
     */
    public $userId;
    public $subject;
    public $userName;
    public $message;
    public $ticketId;

    /**
     * Create a new event instance.
     *
     * @param $ticketId
     * @param $userId
     * @param $subject
     * @param $userName
     * @param $message
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
