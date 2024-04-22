<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Repositories\IU\IuGlobalNotificationRepository;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;

class IuGlobalNotificationsController extends Controller
{

    private IuGlobalNotificationRepository $iuGlobalNotificationRepository;

    public function __construct(IuGlobalNotificationRepository $iuGlobalNotificationRepository)
    {
        $this->iuGlobalNotificationRepository = $iuGlobalNotificationRepository;
    }

    public function getGlobalNotification(int $id)
    {
        $notification = $this->iuGlobalNotificationRepository->getGlobalNotification($id);

        if(!$notification)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        return response()->json($notification, 200);
    }

    public function markGlobalNotificationRead(int $id, Request $request)
    {
        $userId = $request->user()->id;
        
        $notification = $this->iuGlobalNotificationRepository->getGlobalNotification($id);
        if(!$notification)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->iuGlobalNotificationRepository->markGlobalNotificationRead($userId, $id);
        return response()->json(['message' => Lang::get('global_notifications.success.read')], 200);
    }

    public function markGlobalNotificationsModalRead(Request $request)
    {
        $userId = $request->user()->id;
        $this->iuGlobalNotificationRepository->markGlobalNotificationsModalRead($userId);
        return response()->json(['message' => Lang::get('global_notifications.success.bulkModalRead')], 200);
    }
}
