<?php

namespace App\Jobs\IU;

use App\DataObject\QuizData;
use App\Repositories\IuProgressRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUserEntityProgressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $entityId;
    private $entityType;
    private $userId;

    /**
     * Create a new job instance.
     * @param $userId
     * @param $entityId
     * @param $entityType
     */
    public function __construct($userId, $entityId, $entityType)
    {
        $this->userId = $userId;
        $this->entityId = $entityId;
        $this->entityType = $entityType;
    }

    /**
     * Execute the job.
     *
     * @param IuProgressRepository $iuProgressRepository
     * @return void
     */
    public function handle(IuProgressRepository $iuProgressRepository)
    {
        if($this->entityType == QuizData::ENTITY_LESSON)
            $iuProgressRepository->calculateLessonProgress($this->userId, $this->entityId);
        if($this->entityType == QuizData::ENTITY_COURSE_MODULE)
            $iuProgressRepository->calculateCourseModuleProgress($this->userId, $this->entityId);
        if($this->entityType == QuizData::ENTITY_COURSE_LEVEL)
            $iuProgressRepository->calculateCourseLevelProgress($this->userId, $this->entityId);

    }
}
