<?php

namespace App\Http\Middleware\IU;

use App\Repositories\LessonRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuCanAccessLevelEbookList
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->user()->id;

        if (! $this->canAccessLevel($userId, (int) $request->courseId, (int) $request->level)) {
            return response()->json(['errors' => Lang::get('iu.cantAccessLevel')], 403);
        }

        return $next($request);
    }

    public function canAccessLevel($userId, $courseId, $level)
    {
        if ($level === 1) {
            return true;
        }
        $previousLevelProgress = LessonRepository::getUserLevelProgress($userId, $courseId, $level - 1);
        if (! $previousLevelProgress || $previousLevelProgress->progress !== 100) {
            return false;
        }

        return true;
    }
}
