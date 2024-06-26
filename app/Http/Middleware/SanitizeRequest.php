<?php

namespace App\Http\Middleware;

use App\Traits\SanitizeRequestTrait;
use Closure;
use Illuminate\Http\Request;

class SanitizeRequest
{
    use SanitizeRequestTrait;

    /**
     * Handle an incoming request.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $this->sanitize($request->all());
        $request->merge($input);

        return $next($request);
    }
}
