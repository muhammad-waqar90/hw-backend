<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\AfCouponUpdateRequest;
use App\Http\Requests\AF\Coupons\AfCouponCreateRequest;
use App\Http\Requests\AF\Coupons\AfCouponsListRequest;
use App\Repositories\AF\AfCouponRepository;
use App\Repositories\AF\AfCouponRestrictionsRepository;
use App\Transformers\AF\AfCouponTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class AfCouponController extends Controller
{
    private AfCouponRepository $afCouponRepository;

    private AfCouponRestrictionsRepository $afCouponRestrictionsRepository;

    public function __construct(AfCouponRepository $afCouponRepository, AfCouponRestrictionsRepository $afCouponRestrictionsRepository)
    {
        $this->afCouponRepository = $afCouponRepository;
        $this->afCouponRestrictionsRepository = $afCouponRestrictionsRepository;
    }

    public function getCoupon(int $id)
    {
        $coupon = $this->afCouponRepository->getCoupon($id, true);

        $coupon = fractal($coupon, new AfCouponTransformer);

        return response()->json($coupon, 200);
    }

    public function getCouponList(AfCouponsListRequest $request)
    {
        $coupons = $this->afCouponRepository->getCouponList($request->searchText, null, true);

        $fractal = fractal($coupons->getCollection(), new AfCouponTransformer);
        $coupons->setCollection(collect($fractal));

        return response()->json($coupons, 200);
    }

    public function createCoupon(AfCouponCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $coupon = $this->afCouponRepository->createCoupon(
                $request->name,
                $request->description,
                $request->code,
                $request->value,
                $request->value_type,
                $request->status,
                $request->redeem_limit,
                $request->redeem_limit_per_user,
                $request->individual_use
            );

            if ($request->restrictions) {
                foreach ($request->restrictions as $restriction) {
                    $this->afCouponRestrictionsRepository->createCouponRestrictions($coupon->id, $restriction['id'], $restriction['type']);
                }
            }

            DB::commit();

            return response()->json(['message' => Lang::get('general.successfullyCreated', ['model' => 'coupon'])], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: AfCouponController@createCoupon', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function updateCoupon(int $id, AfCouponUpdateRequest $request)
    {
        $coupon = $this->afCouponRepository->getCoupon($id);
        if (! $coupon) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        try {
            $this->afCouponRepository->updateCoupon(
                $id,
                $request->name,
                $request->description,
                $request->status,
                $request->redeem_limit,
                $request->redeem_limit_per_user,
            );

            return response()->json(['message' => Lang::get('general.successfullyUpdated', ['model' => 'coupon'])], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AfCouponController@updateCoupon', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }
}
