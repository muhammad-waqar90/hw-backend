<?php

namespace App\Mail\IU\Ticket;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuTicketResponseEmail extends AbstractMail
{
    public $adminMessage;
    public $ticketSubject;
    public $iuTicketLink;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $adminMessage
     * @param $ticketSubject
     * @param $iuTicketLink
     */

    public function __construct($userProfile, $adminMessage, $ticketSubject, $iuTicketLink)
    {
        parent::__construct($userProfile);
        $this->adminMessage = $adminMessage;
        $this->ticketSubject = $ticketSubject;
        $this->iuTicketLink = $iuTicketLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return $this->subject(Lang::get('email.subjects.ticketResponse'))->view('emails.IU.Ticket.iuTicketResponseEmail');
    }
}
