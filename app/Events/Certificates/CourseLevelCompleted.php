<?php

namespace App\Events\Certificates;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseLevelCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * courser level completed
     *
     * @var $userId
     * @var $entityId
     */
    public $userId;
    public $entityId;

    /**
     * Create a new event instance.
     * @param userId
     * @param entityId
     * @return void
     */
    public function __construct($userId, $entityId)
    {
        $this->userId = $userId;
        $this->entityId = $entityId;
    }
}
