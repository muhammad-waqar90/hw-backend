<?php

namespace App\Console\Commands;

use App\Models\PasswordReset;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class CleanExpiredPasswordResets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:password_resets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired password resets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    /**
     * @var PasswordReset
     */
    private $passwordReset;

    /**
     * CleanExpiredVerifyUser constructor.
     */
    public function __construct(PasswordReset $passwordReset)
    {
        parent::__construct();
        $this->passwordReset = $passwordReset;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->passwordReset->where('created_at', '<', Carbon::now()->subHours(12))
            ->delete();

        $this->info('Cleaning expired password reset codes');
    }
}
