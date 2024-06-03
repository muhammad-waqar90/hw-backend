<?php

namespace App\Console\Commands;

use App\Repositories\AF\AfGlobalNotificationRepository;
use Illuminate\Console\Command;

class ArchiveGlobalNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:global_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive to expired global notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    private AfGlobalNotificationRepository $afGlobalNotificationRepository;

    /**
     * ArchiveGlobalNotifications constructor.
     */
    public function __construct(AfGlobalNotificationRepository $afGlobalNotificationRepository)
    {
        parent::__construct();
        $this->afGlobalNotificationRepository = $afGlobalNotificationRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->afGlobalNotificationRepository->archiveExpiredGlobalNotification();

        $this->info('Expired global notification has been archived');
    }
}
