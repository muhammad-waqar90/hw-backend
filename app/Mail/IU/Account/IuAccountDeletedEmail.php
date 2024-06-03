<?php

namespace App\Mail\IU\Account;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuAccountDeletedEmail extends AbstractMail
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(Lang::get('email.subjects.deleteAccount'))->view('emails.IU.Account.iuAccountDeletedEmail');
    }
}
