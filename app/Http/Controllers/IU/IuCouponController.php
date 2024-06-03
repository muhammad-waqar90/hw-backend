<?php

namespace App\Http\Controllers\IU;

use App\DataObject\CouponData;
use App\Http\Controllers\Controller;
use App\Http\Requests\IuCouponCanRedeemRequest;
use App\Repositories\IU\IuCouponRepository;
use App\Transformers\IU\IuCouponCanRedeemTransformer;
use App\Transformers\IU\IuCouponTransformer;
use Illuminate\Support\Facades\Lang;

class IuCouponController extends Controller
{
    private IuCouponRepository $iuCouponRepository;

    public function __construct(IuCouponRepository $iuCouponRepository)
    {
        $this->iuCouponRepository = $iuCouponRepository;
    }

    public function canRedeem(IuCouponCanRedeemRequest $request)
    {
        $coupon = $this->iuCouponRepository->getCoupon($request->code, CouponData::ACTIVE, true);
        if (! $coupon || ($coupon->redeem_count >= $coupon->redeem_limit)) {
            return response()->json(['errors' => Lang::get('iu.coupon.canNotRedeem')], 404);
        }

        if ($this->iuCouponRepository->validateCartItems($request->cart)) {
            return response()->json(['errors' => Lang::get('iu.purchases.cart.invalidData')], 422);
        }

        $cartItemsCanRedeemCoupon = $this->iuCouponRepository->getCartItemsCanRedeemCoupon($coupon->restrictions, $request->cart);
        if (! count($cartItemsCanRedeemCoupon)) {
            return response()->json(['errors' => Lang::get('iu.coupon.notApplicable')], 422);
        }

        return response()->json([
            'coupon' => fractal($coupon, new IuCouponTransformer()),
            'can_redeem' => fractal($cartItemsCanRedeemCoupon, new IuCouponCanRedeemTransformer()),
        ], 200);
    }
}
