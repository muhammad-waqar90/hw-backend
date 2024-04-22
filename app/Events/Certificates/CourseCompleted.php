<?php

namespace App\Events\Certificates;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Course level completed
     *
     * @var int $userId
     * @var int $entityId
     */
    public $userId;
    public $entityId;

    /**
     * Create a new event instance.
     *
     * @param int $userId
     * @param int $entityId
     * @return void
     */
    public function __construct($userId, $entityId)
    {
        $this->userId = $userId;
        $this->entityId = $entityId;
    }
}
