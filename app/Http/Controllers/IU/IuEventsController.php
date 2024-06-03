<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Http\Requests\IU\IuGetEventsRequest;
use App\Repositories\IU\IuEventsRepository;
use App\Traits\FileSystemsCloudTrait;
use Illuminate\Support\Facades\Lang;

class IuEventsController extends Controller
{
    use FileSystemsCloudTrait;

    private IuEventsRepository $iuEventsRepository;

    public function __construct(IuEventsRepository $iuEventsRepository)
    {
        $this->iuEventsRepository = $iuEventsRepository;
    }

    public function getEventList(IuGetEventsRequest $request)
    {
        $data = $this->iuEventsRepository->getEventsForDates($request->from, $request->to, $request->type);

        return response()->json($data, 200);
    }

    public function getEvent(int $id)
    {
        $event = $this->iuEventsRepository->getEventById($id);

        if (! $event) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $event->img = $event->img ? $this->generateS3Link('events/images/'.$event->img, 1) : null;

        return response()->json($event, 200);
    }
}
