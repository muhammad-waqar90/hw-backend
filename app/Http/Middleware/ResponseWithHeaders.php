<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResponseWithHeaders
{
    /**
     * Handle an incoming request.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $req = $next($request);
        $req->header('Cache-Control', 'no-store');

        return $req;
    }
}
