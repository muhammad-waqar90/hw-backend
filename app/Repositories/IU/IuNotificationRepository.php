<?php

namespace App\Repositories\IU;

use App\DataObject\Notifications\NotificationTypeData;
use App\Models\GlobalNotification;
use App\Models\Notification;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\DB;

class IuNotificationRepository
{
    use NotificationTrait;

    private Notification $notification;

    private GlobalNotification $globalNotification;

    public function __construct(Notification $notification, GlobalNotification $globalNotification)
    {
        $this->notification = $notification;
        $this->globalNotification = $globalNotification;
    }

    /**
     * @return mixed
     */
    public function getMyNotificationList($userId)
    {
        $requestGlobalNotification = $this->globalNotification->select(
            'global_notifications.id',
            'global_notifications.user_id',
            'global_notifications.title',
            'global_notifications.description',
            DB::raw(NotificationTypeData::GLOBAL.' as type'),
            'global_notification_user.notification_read as read',
            'global_notifications.action',
            'global_notifications.created_at'
        )
            ->leftJoin('global_notification_user', function ($query) use ($userId) {
                $query->on('global_notification_user.global_notification_id', '=', 'global_notifications.id')
                    ->where('global_notification_user.user_id', $userId);
            })
            ->where('global_notifications.is_archived', 0)
            ->latest('global_notifications.created_at');

        $requestNotification = $this->notification
            ->select('id', 'user_id', 'title', 'description', 'type', 'read', 'action', 'created_at')
            ->where('user_id', $userId)
            ->latest()
            ->unionall($requestGlobalNotification);

        return $this->notification
            ->select('*')
            ->fromSub($requestNotification, 'notifications')
            ->latest()
            ->simplePaginate(10);
    }
}
