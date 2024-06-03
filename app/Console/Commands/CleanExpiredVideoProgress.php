<?php

namespace App\Console\Commands;

use App\Models\VideoProgress;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class CleanExpiredVideoProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:video_progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired video progress';

    /**
     * @var VideoProgress
     */
    private $videoProgress;

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * CleanExpiredVerifyUser constructor.
     */
    public function __construct(VideoProgress $videoProgress)
    {
        parent::__construct();
        $this->videoProgress = $videoProgress;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->videoProgress->where('created_at', '<', Carbon::now()->subWeeks(4))
            ->delete();

        $this->info('Cleaning expired video progress');
    }
}
