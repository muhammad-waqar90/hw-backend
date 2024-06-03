<?php

namespace App\Console\Commands;

use App\Jobs\CleanExpiredVerifyUserJob;
use App\Models\VerifyUser;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class CleanExpiredVerifyUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:verification_codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired verification codes and delete user data associated with it';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $verifyUser;

    /**
     * CleanExpiredVerifyUser constructor.
     */
    public function __construct(VerifyUser $verifyUser)
    {
        parent::__construct();
        $this->verifyUser = $verifyUser;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expiredVerifications = $this->verifyUser
            ->where('updated_at', '<', Carbon::now()->subHours(24))
            ->get();
        if ($expiredVerifications->isEmpty()) {
            return;
        }
        foreach ($expiredVerifications as $expiredVerification) {
            CleanExpiredVerifyUserJob::dispatch($expiredVerification)->onQueue('low');
        }

        $this->info('Cleaning expired users');
    }
}
