<?php

namespace App\Repositories\IU;

use App\DataObject\Notifications\NotificationTypeData;
use App\Models\GlobalNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IuGlobalNotificationRepository
{
    public GlobalNotification $globalNotification;

    public function __construct(GlobalNotification $globalNotification)
    {
        $this->globalNotification = $globalNotification;
    }

    public function getGlobalNotification($id)
    {
        return $this->globalNotification->where('id', $id)->first();
    }

    public function markGlobalNotificationRead($userId, $globalNotificationId)
    {
        $globalNotification = $this->getGlobalNotification($globalNotificationId);
        $modalRead = $globalNotification->show_modal ?: null;

        $this->globalNotificationReadHelper($userId, $globalNotificationId, $modalRead, 1);
    }

    private function getAllGlobalNotificationsQuery($userId)
    {
        return $this->globalNotification
            ->leftJoin('global_notification_user', function ($query) use ($userId) {
                $query->on('global_notification_user.global_notification_id', '=', 'global_notifications.id')
                    ->where('global_notification_user.user_id', $userId);
            })
            ->where('global_notifications.is_archived', 0);
    }

    public function markAllGlobalNotificationRead($userId)
    {
        $globalNotifications = $this->getAllGlobalNotificationsQuery($userId)
            ->select('global_notifications.id as global_notification_id', 'global_notifications.show_modal as show_modal')
            ->get();

        foreach ($globalNotifications as $globalNotificationUser) {

            $modalRead = $globalNotificationUser['show_modal'] ? $globalNotificationUser['show_modal'] : null;

            $this->globalNotificationReadHelper($userId, $globalNotificationUser->global_notification_id, $modalRead, 1);
        }
    }

    public function countUnreadGlobalNotifications($userId)
    {
        return $this->getAllGlobalNotificationsQuery($userId)
            ->where(function ($query) {
                $query->whereNull('global_notification_user.id')
                    ->orWhereNull('global_notification_user.notification_read');
            })
            ->count();
    }

    public function markGlobalNotificationsModalRead($userId)
    {
        $activeGN = $this->getAllGlobalNotificationsQuery($userId)
            ->select('global_notifications.id as global_notification_id')
            ->where('global_notifications.show_modal', 1)
            ->whereNull('global_notification_user.id')
            ->get();

        foreach ($activeGN as $globalNotificationUser) {
            $this->globalNotificationReadHelper($userId, $globalNotificationUser->global_notification_id, 1);
        }

    }

    public function getModalGlobalNotificationsQuery($userId)
    {
        return $this->getAllGlobalNotificationsQuery($userId)
            ->select('global_notifications.*', DB::raw(NotificationTypeData::GLOBAL.' as type'))
            ->where('global_notifications.show_modal', 1)
            ->whereNull('global_notification_user.id');
    }

    private function globalNotificationReadHelper($userId, $globalNotificationId, $modalRead = null, $read = null)
    {
        DB::table('global_notification_user')->updateOrInsert(
            ['global_notification_id' => $globalNotificationId, 'user_id' => $userId],
            [
                'global_notification_id' => $globalNotificationId,
                'user_id' => $userId,
                'notification_read' => $read,
                'modal_read' => $modalRead,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
