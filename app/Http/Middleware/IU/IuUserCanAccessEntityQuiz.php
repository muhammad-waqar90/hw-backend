<?php

namespace App\Http\Middleware\IU;

use App\DataObject\ErrorCodesData;
use App\DataObject\QuizData;
use App\DataObject\UserProgressData;
use App\Models\CourseLevel;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\UserProgress;
use App\Repositories\IU\IuQuizRepository;
use App\Repositories\IU\IuUserRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class IuUserCanAccessEntityQuiz
{

    /**
     * @var IuQuizRepository
     */
    private $iuQuizRepository;
    /**
     * @var UserProgress
     */
    private $userProgress;

    /**
     * IuUserCanAccessEntityQuiz constructor.
     * @param IuQuizRepository $iuQuizRepository
     * @param UserProgress $userProgress
     */
    public function __construct(IuQuizRepository $iuQuizRepository, UserProgress $userProgress)
    {
        $this->iuQuizRepository = $iuQuizRepository;
        $this->userProgress = $userProgress;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $entityType
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $entityType)
    {
        try {
            $entityId = $this->getEntityId($request, $entityType);
            if(!$entityId)
                return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => ErrorCodesData::QUIZ_ENTITY_ID_NOT_FOUND])], 500);

            if(!$this->getUserOwnsCourse($request->user()->id, $entityId, $entityType))
                return response()->json(['errors' => Lang::get('auth.forbidden')], 403);

            $entityHasQuiz = $this->iuQuizRepository->getEntityHasQuiz($entityId, $entityType);
            if(!$entityHasQuiz)
                return response()->json(['errors' => Lang::get('iu.quiz.noQuizFound')], 404);

            $userProgress = $this->getUserEntityProgress($request->user()->id, $entityId, $entityType);
            if(!$userProgress || $userProgress->progress < UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ) {
                return $this->handleHigherProgressRequired($entityType);
            }

            return $next($request);
        } catch(\Exception $e) {
            Log::error('Exception: IuUserCanAccessEntityQuiz@handle', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }
    }

    private function getEntityId(Request $request, $entityType)
    {
        if($entityType === QuizData::ENTITY_LESSON)
            return (int) $request->lessonId;
        if($entityType === QuizData::ENTITY_COURSE_MODULE)
            return (int) $request->courseModuleId;
        if($entityType === QuizData::ENTITY_COURSE_LEVEL)
            return (int) $request->courseLevelId;

        return null;
    }

    private function getUserEntityProgress($userId, $entityId, $entityType)
    {
        return $this->userProgress->where('user_id', $userId)
            ->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->first();
    }

    private function getUserOwnsCourse($userId, $entityId, $entityType)
    {
        $courseId = null;
        if($entityType === QuizData::ENTITY_LESSON)
            $courseId = Lesson::findOrFail($entityId)->course_id;
        if($entityType === QuizData::ENTITY_COURSE_MODULE)
            $courseId = CourseModule::findOrFail($entityId)->course_id;
        if($entityType === QuizData::ENTITY_COURSE_LEVEL)
            $courseId = CourseLevel::findOrFail($entityId)->course_id;

        if(!$courseId)
            throw new \Exception();

        return IuUserRepository::iuUserOwnsCourse($userId, $courseId);
    }

    private function handleHigherProgressRequired($entityType)
    {
        if($entityType === QuizData::ENTITY_LESSON)
            return response()->json(['errors' => Lang::get('iu.quiz.pleaseWatchVideo')], 400);
        if($entityType === QuizData::ENTITY_COURSE_MODULE)
            return response()->json(['errors' => Lang::get('iu.quiz.pleaseCompleteLessons')], 400);
        if($entityType === QuizData::ENTITY_COURSE_LEVEL)
            return response()->json(['errors' => Lang::get('iu.quiz.pleaseCompleteModules')], 400);
    }
}
