<?php

namespace App\Events\Tickets;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IuAccountRestored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * IU Account Restored
     *
     * @var $userId
     */
    public $userId;

    /**
     * Create a new event instance.
     *
     * @param $userId
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }
}
