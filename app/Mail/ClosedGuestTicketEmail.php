<?php

namespace App\Mail;

class ClosedGuestTicketEmail extends AbstractMail
{
    public $ticketSubject;

    public $adminName;

    public $adminMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($userProfile, $ticketSubject, $adminName, $adminMessage)
    {
        parent::__construct($userProfile);
        $this->ticketSubject = $ticketSubject;
        $this->adminName = $adminName;
        $this->adminMessage = $adminMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.GU.closedGuestTicketEmail');
    }
}
