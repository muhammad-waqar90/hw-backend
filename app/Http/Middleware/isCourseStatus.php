<?php

namespace App\Http\Middleware;

use App\Repositories\AF\AfCourseRepository;
use App\Traits\UtilsTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class isCourseStatus
{
    use UtilsTrait;

    /**
     * @var AfCourseRepository
     */
    private $afCourseRepository;

    /**
     * Handle an incoming request.
     */
    public function __construct(AfCourseRepository $afCourseRepository)
    {
        $this->afCourseRepository = $afCourseRepository;
    }

    public function handle(Request $request, Closure $next, int ...$statuses)
    {
        $courseStatus = $this->afCourseRepository->getCourse($request->id)?->status;
        if (! $this->existInArray($courseStatus, $statuses)) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        return $next($request);
    }
}
