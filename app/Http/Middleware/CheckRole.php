<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (! in_array($request->user()->role_id, explode('|', $role))) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        return $next($request);
    }
}
