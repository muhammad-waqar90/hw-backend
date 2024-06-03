<?php

namespace App\Http\Middleware;

use Closure;
use Vinkla\Shield\ShieldMiddleware;

class PasswordProtected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     *
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        if (config('shield.password_protected')) {
            return app(ShieldMiddleware::class)->handle($request, $next);
        }

        return $next($request);
    }
}
