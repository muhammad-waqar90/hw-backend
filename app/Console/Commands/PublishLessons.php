<?php

namespace App\Console\Commands;

use App\Repositories\AF\AfLessonRepository;
use App\Repositories\AF\AfPublishLessonRepository;
use Illuminate\Console\Command;

class PublishLessons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish:lessons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish lessons which have the publish_at expired and remove entry';

    private AfLessonRepository $afLessonRepository;

    private AfPublishLessonRepository $afPublishLessonRepository;

    /**
     * Create a new command instance.
     */
    public function __construct(AfLessonRepository $afLessonRepository, AfPublishLessonRepository $afPublishLessonRepository)
    {
        parent::__construct();
        $this->afLessonRepository = $afLessonRepository;
        $this->afPublishLessonRepository = $afPublishLessonRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $readyToPublishLessons = $this->afPublishLessonRepository->getReadyToPublishLessons();
        if ($readyToPublishLessons->isEmpty()) {
            return;
        }

        $this->publishLessons($readyToPublishLessons);
        $this->info('Lessons published which have the publish_at expired and entry removed');
    }

    public function publishLessons($lessons)
    {
        $lessonIds = $lessons->pluck('lesson_id');

        $this->afLessonRepository->publishLessons($lessonIds);
        $this->afPublishLessonRepository->removePublishedLesson($lessonIds);
    }
}
