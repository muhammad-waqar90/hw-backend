<?php

namespace App\Events\Courses\CourseModules;

use App\Models\CourseModule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseModuleCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Course module created
     */
    public int $moduleId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CourseModule $courseModule)
    {
        $this->moduleId = $courseModule->id;
    }
}
