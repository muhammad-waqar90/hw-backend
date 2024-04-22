<?php

namespace App\Transformers\AF;

use App\Models\Ticket;
use League\Fractal\TransformerAbstract;
use App\DataObject\Tickets\TicketStatusData;
use App\DataObject\Tickets\TicketCategoryData;

class AfSpecificTicketTransformer extends TransformerAbstract
{
    private $currentUserId;

    /**
     * AfSpecificTicketTransformer constructor.
     * @param $currentUserId
     */
    public function __construct($currentUserId)
    {
        $this->currentUserId = $currentUserId;
    }

    /**
     * A Fractal transformer.
     *
     * @param Ticket $ticket
     * @return array
     */
    public function transform(Ticket $ticket)
    {
        return [
            'id'    => $ticket->id,
            'ticket_category_id' => $ticket->ticket_category_id,
            'ticket_status_id' => $ticket->ticket_status_id,
            'subject'  => $ticket->subject,
            'user'  => $ticket->username ?: $ticket->user_email,
            'user_id'  => $ticket->user_id,
            'log'  => $ticket->log,
            'status' => $ticket->status,
            'claimable' => $ticket->admin_id == null && $ticket->ticket_status_id != TicketStatusData::RESOLVED,
            'can_save_as_lesson_faq' => $ticket->admin_id == $this->currentUserId && $ticket->ticket_status_id == TicketStatusData::RESOLVED && $ticket->ticket_category_id == TicketCategoryData::LESSON_QA,
            'assigned_to_current_user' => $ticket->admin_id == $this->currentUserId,
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at
        ];
    }
}
