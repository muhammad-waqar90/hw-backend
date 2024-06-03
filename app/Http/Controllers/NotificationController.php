<?php

namespace App\Http\Controllers;

use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class NotificationController extends Controller
{
    private NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function markNotificationRead($id, Request $request)
    {
        $userId = $request->user()->id;
        $notification = $this->notificationRepository->getNotification($id);

        if (! $notification) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
        if ($notification->user_id && $notification->user_id != $userId) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }
        if ($notification->read) {
            return response()->json(['errors' => Lang::get('notifications.errors.alreadyRead')], 400);
        }

        $notification->read = 1;
        $notification->save();

        return response()->json(['message' => Lang::get('notifications.success.read'), 'data' => $notification], 200);
    }
}
