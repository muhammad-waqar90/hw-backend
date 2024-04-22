<?php

namespace App\Mail\IU\Ticket;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuTicketUnclaimedEmail extends AbstractMail
{
    public $adminName;
    public $ticketSubject;
    public $ticketId;

    /**
     * Create a new message instance.
     *
     * @param $userProfile
     * @param $ticketSubject
     * @param $adminName
     * @param $ticketId
     */

    public function __construct($userProfile, $ticketSubject, $adminName, $ticketId)
    {
        parent::__construct($userProfile);
        $this->ticketSubject = $ticketSubject;
        $this->adminName = $adminName;
        $this->ticketId = $ticketId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return $this->subject(Lang::get('email.subjects.ticketUnclaimed'))->view('emails.IU.Ticket.iuTicketUnclaimedEmail');
    }
}
