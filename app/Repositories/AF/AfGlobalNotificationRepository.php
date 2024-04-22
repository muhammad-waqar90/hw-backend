<?php

namespace App\Repositories\AF;

use App\Models\GlobalNotification;
use Carbon\Carbon;

class AfGlobalNotificationRepository
{

    private GlobalNotification $globalNotification;

    public function __construct(GlobalNotification $globalNotification)
    {
        $this->globalNotification = $globalNotification;
    }

    public function getNotificationList($searchText = null, $archiveStatus = null){
        return $this->globalNotification
        ->select('global_notifications.*')
        ->when($archiveStatus != null, function ($query) use ($archiveStatus) {
            $query->whereIsArchived($archiveStatus);
        })
        ->when($searchText, function ($query) use ($searchText) {
            $query->where(function ($query) use ($searchText) {
                $query->where('title', 'LIKE', "%$searchText%")
                ->orWhere('description', 'LIKE', "%$searchText%");
            });
        })
        ->with('user', function($query){
            $query->select('id', 'name');
        })
        ->with('adminProfile', function($query){
            $query->select('user_id', 'email');
        })
        ->orderBy('global_notifications.id', 'DESC');
    }

    public function getNotification($id)
    {
        return $this->globalNotification->where('id', $id)->first();
    }

    public function createNotification($title, $description, $body, $userId, $archiveDate, $showModal)
    {
        return $this->globalNotification->create([
            'title'         => $title,
            'description'   => $description,
            'body'          => $body,
            'user_id'       => $userId,
            'archive_at'    => $archiveDate,
            'show_modal'    => $showModal
        ]);
    }

    public function updateGlobalNotification($id, $title, $description, $body, $userId, $archiveDate, $showModal)
    {
        return $this->globalNotification->where('id', $id)->update([
            'title'         => $title,
            'description'   => $description,
            'body'          => $body,
            'user_id'       => $userId,
            'archive_at'    => $archiveDate,
            'is_archived'   => 0,
            'show_modal'    => $showModal
        ]);
    }

    public function archiveExpiredGlobalNotification()
    {
        $this->globalNotification->where("archive_at", '<', Carbon::now())
                                ->where('is_archived', '=', 0)
                                ->update(['is_archived' => 1]);
    }
}
