<?php

namespace App\Events\Tickets;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IuAccountTrashed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * IU Account Trashed (soft delete)
     */
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }
}
