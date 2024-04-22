<?php

namespace App\Transformers\IU;

use App\Models\Ticket;
use League\Fractal\TransformerAbstract;

class IuLessonQaListTransformer extends TransformerAbstract
{

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Ticket $ticket)
    {
        $question = $ticket->ticketMessages[array_key_first($ticket->ticketMessages->toArray())]?->message;
        $answer = $ticket->ticketMessages[array_key_last($ticket->ticketMessages->toArray())]?->message;

        return [
            'id' => $ticket->id,
            'question' => $question ?: null,
            'answer' => $answer ?: null,
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at
        ];
    }
}
