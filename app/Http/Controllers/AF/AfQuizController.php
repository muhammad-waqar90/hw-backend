<?php

namespace App\Http\Controllers\AF;

use App\DataObject\QuizData;
use App\Http\Controllers\Controller;
use App\Repositories\IU\IuQuizRepository;
use App\Transformers\AF\AfQuizTransformer;
use Illuminate\Support\Facades\Lang;

class AfQuizController extends Controller
{
    private IuQuizRepository $iuQuizRepository;

    public function __construct(IuQuizRepository $iuQuizRepository)
    {
        $this->iuQuizRepository = $iuQuizRepository;
    }

    public function getModuleQuiz(int $courseId, int $levelId, int $courseModuleId)
    {
        $quiz = $this->iuQuizRepository->getQuizForEntity($courseModuleId, QuizData::ENTITY_COURSE_MODULE);
        if (! $quiz) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $fractal = fractal($quiz, new AfQuizTransformer);

        return response()->json($fractal, 200);
    }

    public function getLessonQuiz(int $courseId, int $levelId, int $courseModuleId, int $lessonId)
    {
        $quiz = $this->iuQuizRepository->getQuizForEntity($lessonId, QuizData::ENTITY_LESSON);
        if (! $quiz) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $fractal = fractal($quiz, new AfQuizTransformer);

        return response()->json($fractal, 200);
    }
}
