<?php

namespace App\Console\Commands;

use App\Mail\AdminAccountCreatedEmail;
use App\Mail\IU\Purchase\IuPurchaseConfirmationEmail;
use App\Mail\TestEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:testEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test email';

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
     * @return void
     */
    public function handle()
    {
        $email = $this->ask('Input email');
        $defaultIndex = 0;
        $userProfile = User::where('id', 2)->first(); // first_name, last_name
        $type = $this->choice(
            'Select email type?',
            ['AdminAccountCreatedEmail', 'AgeVeritificationEmail', 'ClosedGuestTicketEmail', 'GuestTicketCreatedEmail', 'PasswordResetEmail', 'TestEmail', 'CerificationCodeExpiredEmail', 'VerificationEmail', 'IuCertificateEmail', 'IuPurchaseConfirmationEmail', 'IuTicketClaimedEmail', 'IuTicketCreatedEmail', 'IuTicketResolveEmail', 'IuTicketREsponseEmail', 'IuTicketUnclaimedEmail'],
            $defaultIndex,
            $maxAttempts = null,
            $allowMultipleSelections = false
        );
        if ($type === 'AdminAccountCreatedEmail') {
            Mail::to($email)
                ->queue(new AdminAccountCreatedEmail($userProfile, 'testToken123', 'test'));
        }
        if ($type === 'IuPurchaseConfirmationEmail') {
            $purchaseHistoryId = 1;
            Mail::to($email)
                ->queue(new IuPurchaseConfirmationEmail($userProfile, $purchaseHistoryId));
        }
        if ($type === 'TestEmail') {
            Mail::to($email)
                ->queue(new TestEmail(null, 'mr Email To'));
        }
    }
}
