<?php

namespace App\Transformers\AF;

use App\DataObject\Tickets\TicketStatusData;
use App\Models\Ticket;
use App\Traits\StringManipulationTrait;
use League\Fractal\TransformerAbstract;

class AfTicketTransformer extends TransformerAbstract
{
    use StringManipulationTrait;

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
            'latest_ticket_message' =>  $ticket->latestTicketMessage ? $this->truncate($ticket->latestTicketMessage->message) : null,
            'log'  => $ticket->log,
            'status' => $ticket->status,
            'claimable' => $ticket->admin_id == null && $ticket->ticket_status_id != TicketStatusData::RESOLVED,
            'category_name' => $ticket->categoryName,
            'seen_by_me' => $ticket->seen_by_admin,
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at
        ];
    }
}
