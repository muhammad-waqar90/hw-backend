<?php

namespace App\Http\Middleware\IU;

use App\Traits\UtilsTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response;

class IuUserCanUpdatePaymentMethod
{
    use UtilsTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isMinor = $this->isMinor($request->user()->userProfile->date_of_birth);
        if ($isMinor && $request->paymentMethod) {
            // minor allowed checkout without request of saving payment details i.e: paymentMethod
            $savePaymentMethod = isset($request->paymentMethod['save']) ? $request->paymentMethod['save'] : true;
            if ($savePaymentMethod) {
                return response()->json(['errors' => Lang::get('iu.payment.minorCannotSavePaymentMethod')], 403);
            }
        }

        return $next($request);
    }
}
