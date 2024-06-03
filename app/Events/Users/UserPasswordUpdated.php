<?php

namespace App\Events\Users;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPasswordUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    public $password;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $password)
    {
        $this->userId = $userId;
        $this->password = $password;
    }
}
