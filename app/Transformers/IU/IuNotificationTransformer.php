<?php

namespace App\Transformers\IU;

use App\Models\Notification;
use League\Fractal\TransformerAbstract;

class IuNotificationTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     * @param Notification $notification
     * @return array
     */
    public function transform(Notification $notification)
    {
        return [
            'id'            => $notification->id,
            'title'         => $notification->title,
            'description'   => $notification->description,
            'body'          => $notification->body,
            'type'          => $notification->type,
            'read'          => !!$notification->read,
            'modal_read'    => !!$notification->modal_read,
            'action'        => json_decode($notification->action),
            'created_at'    => $notification->created_at,
        ];
    }
}
