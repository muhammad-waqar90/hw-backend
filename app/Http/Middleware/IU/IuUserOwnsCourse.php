<?php

namespace App\Http\Middleware\IU;

use App\Repositories\IU\IuUserRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuUserOwnsCourse
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! IuUserRepository::iuUserOwnsCourse($request->user()->id, $request->courseId)) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        return $next($request);
    }
}
