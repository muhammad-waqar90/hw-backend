<?php

namespace App\Listeners;

use App\DataObject\QuizData;
use App\Events\Courses\CourseModules\CourseModuleCreated;
use App\Events\Courses\CourseModules\CourseModuleUpdated;
use App\Repositories\AF\AfQuizRepository;

class CourseModuleEventSubscriber
{
    /**
     * @var AfQuizRepository
     */
    private $afQuizRepository;

    /**
     * CourseModuleEventSubscriber constructor.
     * @param AfQuizRepository $afQuizRepository
     */
    public function __construct(AfQuizRepository $afQuizRepository)
    {
        $this->afQuizRepository = $afQuizRepository;
    }

    /**
     * Handle course module created event
     */
    public function handleCourseModuleCreated($event)
    {
        $this->afQuizRepository->updateOrCreateDummyQuiz($event->moduleId, QuizData::ENTITY_COURSE_MODULE);
    }

    /**
     * Handle course module updated event
     */
    public function handleCourseModuleUpdated($event)
    {
        $event->moduleHasExam ?
            $this->afQuizRepository->updateOrCreateDummyQuiz($event->moduleId, QuizData::ENTITY_COURSE_MODULE) :
            $this->afQuizRepository->deleteDummyQuiz($event->moduleId, QuizData::ENTITY_COURSE_MODULE);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            CourseModuleCreated::class,
            [CourseModuleEventSubscriber::class, 'handleCourseModuleCreated']
        );

        $events->listen(
            CourseModuleUpdated::class,
            [CourseModuleEventSubscriber::class, 'handleCourseModuleUpdated']
        );
    }
}
