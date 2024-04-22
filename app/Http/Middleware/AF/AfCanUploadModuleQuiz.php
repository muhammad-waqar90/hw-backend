<?php

namespace App\Http\Middleware\AF;

use App\DataObject\QuizData;
use App\Repositories\IU\IuQuizRepository;
use Closure;
use Illuminate\Http\Request;

class AfCanUploadModuleQuiz
{
    /**
     * @var IuQuizRepository $iuQuizRepository
     */
    private $iuQuizRepository;

    /**
     * AfCanUploadModuleQuiz constructor.
     *
     * @param IuQuizRepository $iuQuizRepository
     */
    public function __construct(IuQuizRepository $iuQuizRepository)
    {
        $this->iuQuizRepository = $iuQuizRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $courseModuleId = $request->route('courseModuleId');

        $moduleHasExam = $this->iuQuizRepository->getEntityHasQuiz($courseModuleId, QuizData::ENTITY_COURSE_MODULE);

        // module_has_exam field should be enabled before uploading the actual exam
        if (!$moduleHasExam)
            return response()->json(['errors' => 'module_has_exam field should be activated before uploading the exam'], 403);

        return $next($request);
    }
}
