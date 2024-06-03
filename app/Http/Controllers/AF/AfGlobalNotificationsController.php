<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\GlobalNotifications\AfGlobalNotificationCreateUpdateRequest;
use App\Http\Requests\AF\GlobalNotifications\AfGlobalNotificationsSearchRequest;
use App\Repositories\AF\AfGlobalNotificationRepository;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class AfGlobalNotificationsController extends Controller
{
    private AfGlobalNotificationRepository $afGlobalNotificationRepository;

    public function __construct(AfGlobalNotificationRepository $afGlobalNotificationRepository)
    {
        $this->afGlobalNotificationRepository = $afGlobalNotificationRepository;
    }

    public function getGlobalNotificationList(AfGlobalNotificationsSearchRequest $request)
    {
        $data = $this->afGlobalNotificationRepository->getNotificationList($request->searchText, $request->archiveStatus)
            ->paginate(20)
            ->appends([
                'searchText' => $request->searchText,
                'archiveStatus' => $request->archiveStatus,
            ]);

        return response()->json($data, 200);
    }

    public function createGlobalNotification(AfGlobalNotificationCreateUpdateRequest $request)
    {
        $this->afGlobalNotificationRepository->createNotification(
            $request->title,
            $request->short_description,
            $request->description,
            $request->user()->id,
            $request->archive_at,
            $request->show_modal
        );

        return response()->json(['message' => Lang::get('global_notifications.success.created')], 200);
    }

    public function getGlobalNotification($id)
    {
        $notification = $this->afGlobalNotificationRepository->getNotification($id);

        if (! $notification) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        return response()->json($notification, 200);
    }

    public function updateGlobalNotification(AfGlobalNotificationCreateUpdateRequest $request, int $id)
    {
        try {
            $notification = $this->afGlobalNotificationRepository->getNotification($id);

            if (! $notification) {
                return response()->json(['errors' => Lang::get('general.notFound')], 404);
            }

            $this->afGlobalNotificationRepository->updateGlobalNotification(
                $id,
                $request->title,
                $request->short_description,
                $request->description,
                $request->user()->id,
                $request->archive_at,
                $request->show_modal
            );

            return response()->json(['message' => Lang::get('global_notifications.success.updated')], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfGlobalNotificationsController@updateGlobalNotification', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => Lang::get('global_notifications.error.invalid')], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function deleteGlobalNotification(int $id)
    {
        $notification = $this->afGlobalNotificationRepository->getNotification($id);
        if (! $notification) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $notification->delete();

        return response()->json(['message' => Lang::get('global_notifications.success.deleted')], 200);
    }
}
