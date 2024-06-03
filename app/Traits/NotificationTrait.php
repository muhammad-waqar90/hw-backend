<?php

namespace App\Traits;

trait NotificationTrait
{
    /**
     * @return mixed
     */
    public function countUnreadNotifications($userId)
    {
        return $this->notification
            ->where('user_id', $userId)
            ->where('read', 0)
            ->count();
    }

    /**
     * @return mixed
     */
    public function markAllNotificationsRead($userId)
    {
        return $this->notification
            ->where('user_id', $userId)
            ->where('read', 0)
            ->update(['read' => 1]);
    }
}
