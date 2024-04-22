<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Repositories\AF\AfInAppTiersRepository;
use App\Transformers\AF\AfInAppTiersAllTransformer;

class AfInAppTiersController extends Controller
{
    private AfInAppTiersRepository $afInAppTiersRepository;

    public function __construct(AfInAppTiersRepository $afInAppTiersRepository)
    {
        $this->afInAppTiersRepository = $afInAppTiersRepository;
    }

    public function getAllTiers()
    {
        $data = $this->afInAppTiersRepository->getAllTiers();
        $tiers = collect(fractal($data, new AfInAppTiersAllTransformer()))->toArray();

        return response()->json($tiers, 200);
    }
}
