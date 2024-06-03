<?php

namespace App\Repositories;

use App\Models\Notification;

class NotificationRepository
{
    private Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return mixed
     */
    public function createNotification($userId, $title, $description, $type, $action = null)
    {
        return $this->notification->create([
            'user_id' => $userId, // any type of user IU | Admin
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'action' => json_encode($action),
        ]);
    }

    /**
     * @return Notification
     */
    public function getNotification($id)
    {
        return $this->notification->find($id);
    }
}
