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
     *
     * @var int $moduleId
     * @var bool $moduleHasExam
     */
    public $moduleId;
    public $moduleHasExam;

    /**
     * Create a new event instance.
     *
     * @param CourseModule $courseModule
     * @return void
     */
    public function __construct(int $courseModuleId, bool $moduleHasExam)
    {
        $this->moduleId = $courseModuleId;
        $this->moduleHasExam = $moduleHasExam;
    }
}
