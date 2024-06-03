<?php

namespace App\Events\Certificates;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseModuleCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * courser module completed
     */
    public int $userId;

    public int $entityId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, int $entityId)
    {
        $this->userId = $userId;
        $this->entityId = $entityId;
    }
}
