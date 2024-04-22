<?php

namespace App\Transformers\IU;

use App\Models\Ticket;
use League\Fractal\TransformerAbstract;

class IuTicketTransformer extends TransformerAbstract
{

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
            'log'  => $ticket->log,
            'status' => $ticket->status,
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at
        ];
    }
}
