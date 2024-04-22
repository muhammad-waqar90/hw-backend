<?php

namespace App\Console\Commands;

use App\Jobs\CleanExpiredVerifyUserAgeJob;
use App\Models\VerifyUserAge;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanExpiredVerifyUserAge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:age_verification_codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired age verification codes and delete user data associated with it';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $verifyUserAge;

    /**
     * CleanExpiredVerifyUserAge constructor.
     * @param VerifyUserAge $verifyUserAge
     */
    public function __construct(VerifyUserAge $verifyUserAge)
    {
        parent::__construct();
        $this->verifyUserAge = $verifyUserAge;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expiredVerifications = $this->verifyUserAge
            ->where('updated_at', '<',Carbon::now()->subHours(24))
            ->get();
        if($expiredVerifications->isEmpty())
            return;
        foreach($expiredVerifications as $expiredVerification)
            CleanExpiredVerifyUserAgeJob::dispatch($expiredVerification)->onQueue('low');

        $this->info('Cleaning expired age verifications');
    }
}
