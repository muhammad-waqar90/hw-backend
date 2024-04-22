<?php

namespace App\Mail;

class AdminAccountCreatedEmail extends AbstractMail
{
    public $token;
    public $userName;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $token
     */
    public function __construct($userProfile, $token, $userName)
    {
        parent::__construct($userProfile);
        $this->token = $token;
        $this->userName = $userName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.Admin.adminAccountCreatedEmail');
    }
}
