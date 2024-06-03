<?php

namespace App\Http\Middleware\IU;

use App\Repositories\LessonRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuUserCanAccessLesson
{
    /**
     * @var LessonRepository
     */
    private $lessonRepository;

    /**
     * Handle an incoming request.
     */
    public function __construct(LessonRepository $lessonRepository)
    {
        $this->lessonRepository = $lessonRepository;
    }

    public function handle(Request $request, Closure $next)
    {
        if (! $this->lessonRepository->get($request->courseId, $request->lessonId)?->published) {
            return response()->json(['errors' => Lang::get('iu.lesson.notPublished')], 403);
        }

        $userId = $request->user()->id;
        $lesson = $this->lessonRepository->getForUser($userId, $request->courseId, $request->lessonId);
        if (! $lesson) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        if (! $this->canAccessLevel($userId, $lesson)) {
            return response()->json(['errors' => Lang::get('iu.cantAccessLevel')], 403);
        }
        if (! $this->canAccessLesson($userId, $lesson)) {
            return response()->json(['errors' => Lang::get('iu.cantAccessLesson')], 403);
        }

        $request->lesson = $lesson;

        return $next($request);
    }

    public function canAccessLevel($userId, $lesson)
    {
        if ($lesson->level === 1) {
            return true;
        }
        $previousLevelProgress = LessonRepository::getUserLevelProgress($userId, $lesson->course_id, $lesson->level - 1);
        if (! $previousLevelProgress || $previousLevelProgress->progress !== 100) {
            return false;
        }

        return true;
    }

    public function canAccessLesson($userId, $lesson)
    {
        if ($lesson->order_id === 1) {
            return true;
        }
        $previousLesson = $this->lessonRepository->getUserPreviousLesson($userId, $lesson);
        if (! $previousLesson || $previousLesson->progress !== 100) {
            return false;
        }

        return true;
    }
}
