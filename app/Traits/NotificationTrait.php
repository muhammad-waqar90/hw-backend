<?php

namespace App\Traits;

trait NotificationTrait
{
    /**
     * @param $userId
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
     * @param $userId
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