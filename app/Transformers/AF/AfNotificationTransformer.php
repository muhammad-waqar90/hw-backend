<?php

namespace App\Transformers\AF;

use App\Models\Notification;
use League\Fractal\TransformerAbstract;

class AfNotificationTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Notification $notification)
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'description' => $notification->description,
            'body' => $notification->body,
            'type' => $notification->type,
            'read' => (bool) $notification->read,
            'modal_read' => (bool) $notification->modal_read,
            'action' => json_decode($notification->action),
            'created_at' => $notification->created_at,
        ];
    }
}
