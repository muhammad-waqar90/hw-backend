<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Repositories\AF\AfNotificationRepository;
use App\Transformers\AF\AfNotificationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class AfNotificationController extends Controller
{

    private AfNotificationRepository $afNotificationRepository;

    public function __construct(AfNotificationRepository $afNotificationRepository)
    {
        $this->afNotificationRepository = $afNotificationRepository;
    }

    public function getMyNotificationList(Request $request)
    {
        $userId = $request->user()->id;

        $notifications = $this->afNotificationRepository->getMyNotificationList($userId);
        $fractal = fractal($notifications->getCollection(), new AfNotificationTransformer());
        $notifications->setCollection(collect($fractal));

        $countUnreadNotifications = $this->afNotificationRepository->countUnreadNotifications($userId);

        $data = collect(['count_unread_notifications' => $countUnreadNotifications]);
        $data = $data->merge($notifications);

        return response()->json($data, 200);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $userId = $request->user()->id;
        $this->afNotificationRepository->markAllNotificationsRead($userId);

        return response()->json(['message' => Lang::get('notifications.success.bulkRead')], 200);
    }
}
