<?php

namespace App\Mail;

class TestEmail extends AbstractMail
{
    public $name;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $name
     */
    public function __construct($userProfile, $name)
    {
        parent::__construct($userProfile);
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Test Email')->view('emails.testEmail');
    }
}
