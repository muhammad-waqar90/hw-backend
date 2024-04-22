<?php

namespace App\Http\Middleware\IU;

use App\DataObject\GDPRStatusData;
use App\Models\UserGdprRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class CheckIfGdprDataIsProcessing
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $isGdprRequestAlreadyInProcess = UserGdprRequest::where('user_id', $request->id)
            ->where('status', GDPRStatusData::PROCESSING)
            ->exists();

        if ($isGdprRequestAlreadyInProcess)
            return response()->json(['errors' => Lang::get('iu.gdprRequest.requestAlreadyInProcess')], 400);

        return $next($request);
    }
}
