<?php

namespace App\Http\Middleware\IU;

use App\Repositories\IU\IuUserProfileRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuHasCompletedProfile
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(!IuUserProfileRepository::getIsProfileCompleted($request->user()->userProfile))
            return response()->json([
                'error' => [
                    'message' => Lang::get('iu.profile.incompleteProfile'),
                    'profileIncomplete' => true
                ]
            ], 400);

        return $next($request);
    }
}
