<?php

namespace App\Http\Middleware\AF;

use App\DataObject\AF\CourseStatusData;
use App\DataObject\QuizData;
use App\Repositories\AF\AfCourseRepository;
use App\Repositories\IU\IuQuizRepository;
use App\Repositories\LessonRepository;
use App\Traits\UtilsTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class AfCanUploadLessonQuiz
{
    use UtilsTrait;

    private AfCourseRepository $afCourseRepository;

    private IuQuizRepository $iuQuizRepository;

    private LessonRepository $lessonRepository;

    public function __construct(
        AfCourseRepository $afCourseRepository,
        IuQuizRepository $iuQuizRepository,
        LessonRepository $lessonRepository
    ) {
        $this->afCourseRepository = $afCourseRepository;
        $this->iuQuizRepository = $iuQuizRepository;
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         * Can Upload Lesson Quiz :: if
         * - Course status DRAFT
         * - Course status COMING_SOON
         * - Lesson UN-PUBLISHED
         *
         * Can Update PUBLISHED Lesson Quiz :: if
         * - Lesson PUBLISHED
         * - Already have QUIZ
         */
        $courseId = $request->route('id');
        $lessonId = $request->route('lessonId');

        $course = $this->afCourseRepository->getCourse($courseId);
        $lesson = $this->lessonRepository->get($course?->id, $lessonId);
        if (! $lesson) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        if (! $this->existInArray($course->status, [CourseStatusData::DRAFT, CourseStatusData::COMING_SOON]) && $lesson->published) {
            if (! $this->iuQuizRepository->getEntityHasQuiz($lesson->id, QuizData::ENTITY_LESSON)) {
                return response()->json(['errors' => 'Can not import quiz to published lesson with no previous quiz'], 403);
            }
        }

        return $next($request);
    }
}
