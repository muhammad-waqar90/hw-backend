<?php

namespace App\Mail;

class ForgotUsernameEmail extends AbstractMail
{
    public $username;

    /**
     * Create a new message instance.
     */
    public function __construct($userProfile, $username)
    {
        parent::__construct($userProfile);
        $this->username = $username;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.forgotUsernameEmail');
    }
}
