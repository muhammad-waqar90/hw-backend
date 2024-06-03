<?php

namespace App\Mail;

class PasswordResetEmail extends AbstractMail
{
    public $token;

    /**
     * Create a new message instance.
     */
    public function __construct($userProfile, $token)
    {
        parent::__construct($userProfile);
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.passwordResetEmail');
    }
}
