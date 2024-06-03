<?php

namespace App\Events\Courses\CourseModules;

use App\Models\CourseModule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseModuleUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Course module updated
     */
    public int $moduleId;

    public bool $moduleHasExam;

    /**
     * Create a new event instance.
     */
    public function __construct(int $courseModuleId, bool $moduleHasExam)
    {
        $this->moduleId = $courseModuleId;
        $this->moduleHasExam = $moduleHasExam;
    }
}
