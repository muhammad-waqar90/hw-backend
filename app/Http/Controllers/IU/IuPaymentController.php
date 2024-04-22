<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Repositories\IU\IuPaymentRepository;
use App\Transformers\IU\Payment\IuCustomerTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuPaymentController extends Controller
{

    private IuPaymentRepository $iuPaymentRepository;

    public function __construct(IuPaymentRepository $iuPaymentRepository)
    {
        $this->iuPaymentRepository = $iuPaymentRepository;
    }

    public function getSetupIntent(Request $request)
    {
        try {
            $customer = $this->iuPaymentRepository->updateOrCreateCustomer($request->user());
            return $customer->createSetupIntent();
        }catch(\Exception $e) {
            return response()->json(['errors' => Lang::get('general.wentWrong')], 400);
        }
    }

    public function getPaymentMethod(Request $request)
    {
        $data = $this->iuPaymentRepository->updateOrCreateCustomer($request->user());
        $fractal = fractal($data, new IuCustomerTransformer());

        return response()->json($fractal, 200);
    }

    public function updatePaymentMethod(Request $request)
    {
        $customer = $this->iuPaymentRepository->updateOrCreateCustomer($request->user());
        $customer->createOrGetStripeCustomer();

        $customer->updateDefaultPaymentMethod($request->paymentMethod);
        $customer->updateDefaultPaymentMethodFromStripe();

        return response()->json(['message' => Lang::get('iu.payment.successfullyUpdatedPaymentMethod')], 200);
    }

    public function deletePaymentMethod(Request $request)
    {
        $customer = $this->iuPaymentRepository->updateOrCreateCustomer($request->user());
        $customer->deletePaymentMethods();

        return response()->json(['message' => Lang::get('iu.payment.successfullyDeletedPaymentMethod')], 200);
    }
}
