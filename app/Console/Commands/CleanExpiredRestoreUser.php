<?php

namespace App\Console\Commands;

use App\DataObject\IuDeleteAccountData;
use App\Jobs\IU\CleanExpiredRestoreUserJob;
use App\Mail\IU\Account\IuAccountDeletedEmail;
use App\Models\RestoreUser;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class CleanExpiredRestoreUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:restore_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired restore users, soft delete, prefix email and expire links';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * 1- CleanExpiredRestoreUserJob
     *   - prefix email (account re-registration)
     *   - expire links (GDPR ...)
     *   - soft delete user (update deleted_at)
     *   - delete restore users (token)
     *
     * 2- Mail
     *   - send account deleted email
     *
     * @return bool
     */
    public function handle()
    {
        $expiredRestoreUsers = $this->getExpiredRestoreUsers();
        if ($expiredRestoreUsers->isEmpty()) {
            return;
        }

        foreach ($expiredRestoreUsers as $expiredRestoreUser) {
            CleanExpiredRestoreUserJob::dispatch($expiredRestoreUser->user_id, $expiredRestoreUser->userProfile->email, $expiredRestoreUser->token);
            Mail::to($expiredRestoreUser->userProfile->email)->queue(new IuAccountDeletedEmail());
        }

        $this->info('Clean expired restore users, soft delete, prefix email and expire links');

        return true;
    }

    private function getExpiredRestoreUsers()
    {
        return RestoreUser::where('updated_at', '<', Carbon::now()->subDays(IuDeleteAccountData::ACCOUNT_RESTORE_DAYS_LIMIT))
            ->with('userProfile')
            ->get();
    }
}
