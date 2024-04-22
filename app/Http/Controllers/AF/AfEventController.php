<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Events\AfEventsCreateUpdateRequest;
use App\Http\Requests\AF\Events\AfEventsListRequest;
use App\Repositories\AF\AfEventRepository;
use App\Transformers\AF\AfEventTransformer;
use App\Traits\FileSystemsCloudTrait;
use Illuminate\Support\Facades\Lang;

class AfEventController extends Controller
{
    use FileSystemsCloudTrait;

    private AfEventRepository $afEventRepository;

    public function __construct(AfEventRepository $afEventRepository)
    {
        $this->afEventRepository = $afEventRepository;
    }

    public function createEvent(AfEventsCreateUpdateRequest $request)
    {
        $img = $request->img ? $this->uploadFile($this->afEventRepository->getImageS3StoragePath(), $request->img) : null;
        $this->afEventRepository->createEvent(
            $request->title,
            $request->description,
            $request->type,
            $img,
            $request->url,
            $request->start_date,
            $request->end_date,
        );

        return response()->json(['message' => Lang::get('general.successfullyCreated', ['model' => 'event'])], 200);
    }

    public function getEventsList(AfEventsListRequest $request)
    {
        $events = $this->afEventRepository->getEventList($request->searchText, $request->type);

        $fractal = fractal($events->getCollection(), new AfEventTransformer);
        $events->setCollection(collect($fractal));
        return response()->json($events, 200);
    }

    public function getEvent(int $id)
    {
        $event = $this->afEventRepository->getEvent($id);
        if(!$event)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $event = fractal($event, new AfEventTransformer);
        return response()->json($event, 200);
    }

    public function updateEvent(AfEventsCreateUpdateRequest $request, int $id)
    {
        $event = $this->afEventRepository->getEvent($id);
        if(!$event)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $img = $request->img ? $this->updateFile($this->afEventRepository->getImageS3StoragePath(), $event->img, $request->img) : $event->img;

        $this->afEventRepository->updateEvent(
            $id,
            $request->title,
            $request->description,
            $request->type,
            $img,
            $request->url,
            $request->start_date,
            $request->end_date,
        );

        return response()->json(['message' => Lang::get('general.successfullyUpdated', ['model' => 'event'])], 200);
    }

    public function deleteEvent(int $id)
    {
        $event = $this->afEventRepository->getEvent($id);
        if(!$event)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->afEventRepository->deleteEvent($id);

        return response()->json(['message' => Lang::get('general.successfullyDeleted', ['model' => 'event'])], 200);
    }
}
