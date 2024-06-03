<?php

namespace App\Http\Middleware\AF;

use App\DataObject\AF\CourseStatusData;
use App\DataObject\QuizData;
use App\Repositories\AF\AfCourseRepository;
use App\Repositories\IU\IuQuizRepository;
use App\Traits\UtilsTrait;
use Closure;
use Illuminate\Http\Request;

class AfCanUpdateModuleHasExam
{
    use UtilsTrait;

    /**
     * @var AfCourseRepository
     */
    private $afCourseRepository;

    /**
     * @var IuQuizRepository
     */
    private $iuQuizRepository;

    public function __construct(AfCourseRepository $afCourseRepository, IuQuizRepository $iuQuizRepository)
    {
        $this->afCourseRepository = $afCourseRepository;
        $this->iuQuizRepository = $iuQuizRepository;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $courseId = $request->route('id');
        $courseModuleId = $request->route('courseModuleId');

        $moduleHasExam = $this->iuQuizRepository->getEntityHasQuiz($courseModuleId, QuizData::ENTITY_COURSE_MODULE);

        $course = $this->afCourseRepository->getCourse($courseId);

        // cannot change module_has_exam if course->status not DRAFT or COMING_SOON
        if (((bool) $request->module_has_exam !== $moduleHasExam) && (! $this->existInArray($course->status, [CourseStatusData::DRAFT, CourseStatusData::COMING_SOON]))) {
            return response()->json(['errors' => 'module_has_exam field cannot be changed as course status is not DRAFT or COMING_SOON'], 403);
        }

        return $next($request);
    }
}
