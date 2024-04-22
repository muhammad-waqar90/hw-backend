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
     *
     * @var int $moduleId
     */
    public $moduleId;

    /**
     * Create a new event instance.
     *
     * @param CourseModule $courseModule
     * @return void
     */
    public function __construct(CourseModule $courseModule)
    {
        $this->moduleId = $courseModule->id;
    }
}
