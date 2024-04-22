<?php

namespace App\Transformers\IU;

use App\Models\Ticket;
use App\Traits\StringManipulationTrait;
use League\Fractal\TransformerAbstract;

class IuMyTicketListTransformer extends TransformerAbstract
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
            'admin_name'  => $ticket->adminName,
            'latest_ticket_message' =>  $ticket->latestTicketMessage ? $this->truncate($ticket->latestTicketMessage->message) : null,
            'log'  => $ticket->log,
            'status' => $ticket->status,
            'seen_by_me' => $ticket->seen_by_user,
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at,
            'type'  => $ticket->latestTicketMessage->type
        ];
    }

}
