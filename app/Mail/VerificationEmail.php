<?php

namespace App\Mail;

class VerificationEmail extends AbstractMail
{
    public $userProfile;
    public $token;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $token
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
        return $this->view('emails.verificationEmail');
    }
}
