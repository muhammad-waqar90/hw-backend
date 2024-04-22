<?php

namespace App\Http\Middleware\IU;

use App\Models\EbookAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuCanAccessEbook
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
        $canAccess = EbookAccess::where('user_id', $request->user()->id)
            ->where('course_module_id', $request->lesson->course_module_id)
            ->exists();
        if (!$canAccess)
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);

        return $next($request);
    }
}
