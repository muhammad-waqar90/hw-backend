<?php

namespace App\Mail\IU\Ticket;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuTicketResolveEmail extends AbstractMail
{
    public $ticketSubject;
    public $adminName;
    public $adminMessage;
    public $ticketId;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $ticketSubject
     * @param $adminName
     * @param $adminMessage
     * @param $ticketId
     */

    public function __construct($userProfile, $ticketSubject, $adminName, $adminMessage, $ticketId)
    {
        parent::__construct($userProfile);
        $this->ticketSubject = $ticketSubject;
        $this->adminName = $adminName;
        $this->adminMessage = $adminMessage;
        $this->ticketId = $ticketId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(Lang::get('email.subjects.ticketResolve'))->view('emails.IU.Ticket.iuTicketResolveEmail');
    }
}
