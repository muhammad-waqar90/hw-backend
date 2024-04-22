<?php

namespace App\Http\Controllers\IU;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\IU\SalaryScale\IuCreateUserSalaryScaleRequest;
use App\Http\Requests\IU\SalaryScale\IuUpdateUserSalaryScaleRequest;
use App\Repositories\IU\IuSalaryScaleRepository;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class IuSalaryScaleController extends Controller
{
    private IuSalaryScaleRepository $iuSalaryScaleRepository;

    public function __construct(IuSalaryScaleRepository $iuSalaryScaleRepository)
    {
        $this->iuSalaryScaleRepository = $iuSalaryScaleRepository;
    }

    public function getDiscountedCountryList()
    {
        $data = $this->iuSalaryScaleRepository->getDiscountedCountryList();
        return response()->json($data, 200);
    }

    public function createSalaryScale(IuCreateUserSalaryScaleRequest $request)
    {
        $userId = $request->user()->id;
        try {
            $this->iuSalaryScaleRepository->createUserSalaryScale(
                $userId,
                $request->discounted_country_id,
                $request->discounted_country_range_id,
                $request->declaration
            );

            return response()->json(['message' => Lang::get('general.successfullyCreated', ['model' => 'user salary scale'])], 200);
        } catch (Exception $e) {
            Log::error('Exception: IuSalaryScaleController@createSalaryScale', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function updateSalaryScale(IuUpdateUserSalaryScaleRequest $request)
    {
        $userId = $request->user()->id;
        if(!$request->user()->salaryScale)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        try {
            $this->iuSalaryScaleRepository->updateUserSalaryScale($userId, $request->discounted_country_id, $request->discounted_country_range_id);

            return response()->json(['message' => Lang::get('general.successfullyUpdated', ['model' => 'user salary scale'])], 200);
        } catch (Exception $e) {
            Log::error('Exception: IuSalaryScaleController@updateSalaryScale', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }
}
