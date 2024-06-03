<?php

namespace App\Transformers\IU;

use App\DataObject\Tickets\TicketMessageTypeData;
use App\Models\TicketMessage;
use App\Repositories\TicketRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class IuTicketMessageTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(TicketMessage $ticketMessage)
    {
        return [
            'id' => $ticketMessage->id,
            'user_id' => $ticketMessage->user_id,
            'ticket_id' => $ticketMessage->ticket_id,
            'message' => $ticketMessage->message,
            'type' => $ticketMessage->type,
            'created_at' => $ticketMessage->created_at,
            'updated_at' => $ticketMessage->updated_at,
            'username' => $ticketMessage?->username,
            'img' => $ticketMessage->type === TicketMessageTypeData::ADMIN_ASSET_MESSAGE || $ticketMessage->type === TicketMessageTypeData::USER_ASSET_MESSAGE ? $this->generateS3Link(TicketRepository::getThumbnailS3StoragePath($ticketMessage->ticket_id).$ticketMessage->message, 1) : null,
        ];
    }
}
