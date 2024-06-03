<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Repositories\IU\IuGlobalNotificationRepository;
use App\Repositories\IU\IuNotificationRepository;
use App\Transformers\IU\IuNotificationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuNotificationController extends Controller
{
    private IuNotificationRepository $iuNotificationRepository;

    private IuGlobalNotificationRepository $iuGlobalNotificationRepository;

    public function __construct(IuNotificationRepository $iuNotificationRepository, IuGlobalNotificationRepository $iuGlobalNotificationRepository)
    {
        $this->iuNotificationRepository = $iuNotificationRepository;
        $this->iuGlobalNotificationRepository = $iuGlobalNotificationRepository;
    }

    public function getMyNotificationList(Request $request)
    {
        $userId = $request->user()->id;
        $countUnread = $this->iuNotificationRepository->countUnreadNotifications($userId) + $this->iuGlobalNotificationRepository->countUnreadGlobalNotifications($userId);

        $notification = $this->iuNotificationRepository->getMyNotificationList($userId);
        $fractal = fractal($notification->getCollection(), new IuNotificationTransformer());
        $notification->setCollection(collect($fractal));

        $data = ['count_unread_notifications' => $countUnread];
        if ($request->page == null || $request->page == 0) {
            $data['global_notification_modal'] = $this->iuGlobalNotificationRepository->getModalGlobalNotificationsQuery($userId)->get();
        }

        return response()->json(collect($data)->merge($notification), 200);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $userId = $request->user()->id;
        $this->iuNotificationRepository->markAllNotificationsRead($userId);
        $this->iuGlobalNotificationRepository->markAllGlobalNotificationRead($userId);

        return response()->json(['message' => Lang::get('notifications.success.bulkRead')], 200);
    }
}
