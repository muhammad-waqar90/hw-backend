<?php

namespace App\Mail\IU\Ticket;

use App\Mail\AbstractMail;
use Illuminate\Support\Facades\Lang;

class IuTicketCreatedEmail extends AbstractMail
{
    public $userMessage;

    public $ticketSubject;

    public $ticketId;

    /**
     * Create a new message instance.
     */
    public function __construct($userProfile, $userMessage, $ticketSubject, $ticketId)
    {
        parent::__construct($userProfile);
        $this->userMessage = $userMessage;
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
        return $this->subject(Lang::get('email.subjects.ticketCreated'))->view('emails.IU.Ticket.iuTicketCreatedEmail');
    }
}
