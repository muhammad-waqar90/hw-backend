<?php

namespace App\Jobs;

use App\Models\VerifyUser;
use App\Repositories\AuthenticationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanExpiredVerifyUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var VerifyUser
     */
    private $verifyUser;

    /**
     * Create a new job instance.
     *
     * @param VerifyUser $verifyUser
     */
    public function __construct(VerifyUser $verifyUser)
    {
        $this->verifyUser = $verifyUser;
    }

    /**
     * Execute the job.
     *
     * @param AuthenticationRepository $authenticationRepository
     * @return void
     */
    public function handle(AuthenticationRepository $authenticationRepository)
    {
        $authenticationRepository->onVerificationExpire($this->verifyUser);
    }
}
