<?php

namespace App\Http\Middleware\IU;

use App\Models\CourseModule;
use App\Repositories\LessonRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuCanAccessModule
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->user()->id;
        $canAccess = $this->canAccessLevel($userId, $request->courseModuleId);
        if (! $canAccess) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        return $next($request);
    }

    public function canAccessLevel($userId, $courseModuleId)
    {
        $courseModule = CourseModule::where('id', $courseModuleId)
            ->with('courseLevel')
            ->first();

        if ($courseModule->courseLevel->value === 1) {
            return true;
        }

        $previousLevelProgress = LessonRepository::getUserLevelProgress($userId, $courseModule->course_id, $courseModule->courseLevel->value - 1);
        if (! $previousLevelProgress || $previousLevelProgress->progress !== 100) {
            return false;
        }

        return true;
    }
}
