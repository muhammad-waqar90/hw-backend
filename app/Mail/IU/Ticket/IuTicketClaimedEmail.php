<?php

namespace App\Mail\IU\Ticket;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuTicketClaimedEmail extends AbstractMail
{
    public $adminName;

    public $ticketSubject;

    public $ticketId;

    /**
     * Create a new message instance.
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
        return $this->subject(Lang::get('email.subjects.ticketClaimed'))->view('emails.IU.Ticket.iuTicketClaimedEmail');
    }
}
