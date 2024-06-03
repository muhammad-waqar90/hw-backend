<?php

namespace App\Mail\IU\Account;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuAccountTrashedEmail extends AbstractMail
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userProfile)
    {
        parent::__construct($userProfile);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(Lang::get('email.subjects.deleteAccount'))->view('emails.IU.Account.iuAccountTrashedEmail');
    }
}
