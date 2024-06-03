<?php

namespace App\Http\Middleware\IU;

use App\DataObject\IdentityVerificationStatusData;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuIdentityVerified
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $identityVerification = $request->user()->identityVerification;
        if (! $identityVerification || $identityVerification->status !== IdentityVerificationStatusData::COMPLETED) {
            return response()->json([
                'error' => [
                    'message' => Lang::get('iu.identityVerification.incompleteIdentity'),
                    'identityUnverified' => true,
                    'identityVerificationStatus' => $identityVerification ? $identityVerification->status : IdentityVerificationStatusData::PENDING,
                ],
            ], 400);
        }

        return $next($request);
    }
}
