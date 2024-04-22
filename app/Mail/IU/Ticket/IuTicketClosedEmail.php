<?php

namespace App\Mail\IU\Ticket;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuTicketClosedEmail extends AbstractMail
{
    public $ticketSubject;
    public $ticketId;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $ticketSubject
     * @param $ticketId
     */

    public function __construct($userProfile, $ticketSubject, $ticketId)
    {
        parent::__construct($userProfile);
        $this->ticketSubject = $ticketSubject;
        $this->ticketId = $ticketId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(Lang::get('email.subjects.ticketClosed'))->view('emails.IU.Ticket.iuTicketClosedEmail');
    }
}
