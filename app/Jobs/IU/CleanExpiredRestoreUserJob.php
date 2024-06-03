<?php

namespace App\Jobs\IU;

use App\Repositories\GdprRepository;
use App\Repositories\IU\IuUserRepository;
use App\Repositories\TicketRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * - prefix email (account re-registration)
 * - soft delete user (update deleted_at)
 * - expire links (GDPR ...)
 * - delete restore users (token)
 */
class CleanExpiredRestoreUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $userId;

    private $email;

    private $token;

    public function __construct($userId, $email, $token)
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IuUserRepository $iuUserRepository, GdprRepository $gdprRepository, TicketRepository $ticketRepository)
    {
        $iuUserRepository->onExpiredRestoreUser($this->userId, $this->email, $this->token);
        $gdprRepository->onExpiredRestoreUser($this->userId);
    }
}
