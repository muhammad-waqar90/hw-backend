<?php

namespace App\Console\Commands;

use App\DataObject\GDPRStatusData;
use App\Models\UserGdprRequest;
use App\Repositories\GdprRepository;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class CleanExpiredUserGdprExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:gdpr_exports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired GDPR exports';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $gdprRepository;

    public function __construct(UserGdprRequest $userGdprRequest, GdprRepository $gdprRepository)
    {
        parent::__construct();
        $this->userGdprRequest = $userGdprRequest;
        $this->gdprRepository = $gdprRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $expiredGdprRequests = $this->getExpiredGdprRequests();
        if ($expiredGdprRequests->isEmpty()) {
            return;
        }

        $this->gdprRepository->removeExpiredGdprExports($expiredGdprRequests);
        $this->info('Expired GDPR exports cleaned');
    }

    public function getExpiredGdprRequests()
    {
        return $this->userGdprRequest
            ->where('status', GDPRStatusData::READY)
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->get();
    }
}
