<?php

namespace App\Mail;

class AgeVerificationEmail extends AbstractMail
{

    public $childProfile;
    public $token;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $childProfile
     * @param $token
     */
    public function __construct($user, $childProfile, $token)
    {
        parent::__construct(null);
        $this->childProfile = $childProfile;
        $this->token = $token;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.ageVerificationEmail');
    }
}
