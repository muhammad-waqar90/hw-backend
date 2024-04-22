<?php

namespace App\Http\Controllers\IU;

use App\DataObject\AdvertData;
use App\Models\Advert;
use App\Transformers\IU\IuAdvertTransformer;

class IuAdvertController
{
    private Advert $advert;

    public function __construct(Advert $advert)
    {
        $this->advert = $advert;
    }

    public function getAdvertList(): \Illuminate\Http\JsonResponse
    {
        $data = $this->advert
            ->where('status', AdvertData::STATUS_ACTIVE)
            ->orderBy('priority', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        $fractal = fractal($data, new IuAdvertTransformer());
        return response()->json($fractal, 200);
    }
}
