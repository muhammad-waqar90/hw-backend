<?php


namespace App\Repositories\AF;

use App\Models\Notification;
use App\Traits\NotificationTrait;

class AfNotificationRepository
{
    use NotificationTrait;

    private Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getMyNotificationList($userId)
    {
        return $this->notification
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->simplePaginate(10);
    }

    public function deleteNotifications($userId, $courseIds, $type)
    {
        return $this->notification
            ->where('user_id', $userId)
            ->where('type', $type)
            ->whereIn('action->redirect->courseId', $courseIds)
            ->delete();
    }
}
