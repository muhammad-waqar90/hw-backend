<?php

namespace App\Http\Middleware\IU;

use App\DataObject\ErrorCodesData;
use App\DataObject\QuizData;
use App\Repositories\IU\IuQuizRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuHasEntityExamAccess
{

    /**
     * @var IuQuizRepository
     */
    private $iuQuizRepository;

    public function __construct(IuQuizRepository $iuQuizRepository)
    {
        $this->iuQuizRepository = $iuQuizRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $entityType
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $entityType)
    {
        $entityId = $this->getEntityId($request, $entityType);
        if(!$entityId)
            return response()->json(
                [
                    'errors' => Lang::get('general.pleaseContactSupportWithCode',
                    ['code' => ErrorCodesData::QUIZ_ENTITY_ID_NOT_FOUND])
                ],
                500);

        $quiz = $this->iuQuizRepository->getQuizForEntity($entityId, $entityType);
        if(!$quiz->price)
            return $next($request);
        if(!$this->iuQuizRepository->userCanAccessExam($request->user()->id, $quiz->id))
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);

        return $next($request);
    }

    private function getEntityId(Request $request, $entityType)
    {
        if($entityType === QuizData::ENTITY_COURSE_MODULE)
            return (int) $request->courseModuleId;
        if($entityType === QuizData::ENTITY_COURSE_LEVEL)
            return (int) $request->courseLevelId;

        return null;
    }
}
