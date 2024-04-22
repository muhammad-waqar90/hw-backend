<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use JWTAuth;

class CheckActive
{

    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()->is_enabled || $request->user()->restoreUser) {
            JWTAuth::manager()->invalidate(new \Tymon\JWTAuth\Token($request->bearerToken()));
            return response()->json(['errors' => Lang::get('auth.accountDisabled')], 401);
        }

        return $next($request);
    }
}
