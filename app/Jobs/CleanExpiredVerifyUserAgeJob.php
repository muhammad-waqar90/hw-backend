<?php

namespace App\Jobs;

use App\Models\VerifyUserAge;
use App\Repositories\AuthenticationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanExpiredVerifyUserAgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var VerifyUserAge
     */
    private $verifyUserAge;

    /**
     * Create a new job instance.
     *
     * @param VerifyUserAge $verifyUserAge
     */
    public function __construct(VerifyUserAge $verifyUserAge)
    {
        $this->verifyUserAge = $verifyUserAge;
    }

    /**
     * Execute the job.
     *
     * @param AuthenticationRepository $authenticationRepository
     * @return void
     */
    public function handle(AuthenticationRepository $authenticationRepository)
    {
        $authenticationRepository->onAgeVerificationExpire($this->verifyUserAge);
    }
}
