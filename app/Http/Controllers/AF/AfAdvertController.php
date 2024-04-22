<?php

namespace App\Http\Controllers\AF;

use App\DataObject\AdvertData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Adverts\AfAdvertCreateRequest;
use App\Http\Requests\AF\Adverts\AfAdvertSearchRequest;
use App\Http\Requests\AF\Adverts\AfAdvertSortingRequest;
use App\Http\Requests\AF\Adverts\AfAdvertUpdateRequest;
use App\Repositories\AF\AfAdvertRepository;
use App\Transformers\AF\AfAdvertTransformer;
use App\Traits\FileSystemsCloudTrait;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AfAdvertController extends Controller
{
    use FileSystemsCloudTrait;

    private AfAdvertRepository $afAdvertRepository;

    public function __construct(AfAdvertRepository $afAdvertRepository)
    {
        $this->afAdvertRepository = $afAdvertRepository;
    }

    public function getAdvertList(AfAdvertSearchRequest $request)
    {
        $data = $this->afAdvertRepository->getAdvertList($request->searchText, $request->status)
                ->paginate(20)
                ->appends([
                    'searchText'  => $request->searchText,
                    'status' => $request->status
                ]);

        $fractal = fractal($data->getCollection(), new AfAdvertTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function createAdvert(AfAdvertCreateRequest $request)
    {
        $imageName = $this->uploadAdvert($request);
        $this->afAdvertRepository->createAdvert($request->name, $request->url, $imageName, $request->status, $request->expires_at);
        return response()->json(['message' => Lang::get('advert.success.created')], 200);
    }

    public function getAdvert($id)
    {
        $advert = $this->afAdvertRepository->getAdvert($id);

        if(!$advert)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $advert->img = $this->generateS3Link('adverts/images/'.$advert->img, AdvertData::DEFAULT_ADVERT_EXPIRY_DAYS);
        return response()->json($advert, 200);
    }

    public function updateAdvert(AfAdvertUpdateRequest $request, $id)
    {
        try {
            $advert = $this->afAdvertRepository->getAdvert($id);

            if(!$advert)
                return response()->json(['errors' => Lang::get('general.notFound')], 404);

            $imageName = $advert->img;
            if($request->img) {
                Storage::disk(config('filesystems.cloud'))->delete("adverts/images/$imageName");
                $imageName = $this->uploadAdvert($request);
            }

            $priority = $advert->status != (int)$request->status ? AdvertData::DEFAULT_PRIORITY : $advert->priority;

            $this->afAdvertRepository->updateAdvert($id, $request->name, $imageName, $request->url, $priority, $request->expires_at, $request->status);
            return response()->json(['message' => Lang::get('advert.success.updated')], 200);
        } catch(\Exception $e) {
            Log::error('Exception: AfAdvertController@updateAdvert', [$e->getMessage()]);
            if($e->getCode() == 23000)
                return response()->json(['errors' => Lang::get('advert.error.invalid')], 400);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function deleteAdvert($id)
    {
        $advert = $this->afAdvertRepository->getAdvert($id);
        if(!$advert)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        Storage::disk(config('filesystems.cloud'))->delete("adverts/images/$advert->img");
        $advert->delete();
        return response()->json(['message' => Lang::get('advert.success.deleted')], 200);
    }

    public function sortingAdvert(AfAdvertSortingRequest $request) {
        $this->afAdvertRepository->sortingAdvert($request->data);
        return response()->json(['message' => Lang::get('advert.success.sorted')], 200);
    }

    private function uploadAdvert($request) {
        $extension = $request->file('img')->extension();
        $imageName = Str::uuid().".".$extension;
        Storage::disk(config('filesystems.cloud'))->putFileAs('adverts/images/', $request->img, $imageName);
        return $imageName;
    }
}
