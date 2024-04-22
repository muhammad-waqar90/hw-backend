<?php

namespace App\Mail;

class GuestTicketCreatedEmail extends AbstractMail
{
    public $userMessage;
    public $ticketSubject;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $userMessage
     * @param $ticketSubject
     */

    public function __construct($userProfile, $userMessage, $ticketSubject)
    {
        parent::__construct($userProfile);
        $this->userMessage = $userMessage;
        $this->ticketSubject = $ticketSubject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return $this->view('emails.GU.guestTicketCreatedEmail');
    }
}
