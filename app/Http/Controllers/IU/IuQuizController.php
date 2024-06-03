<?php

namespace App\Http\Controllers\IU;

use App\DataObject\QuizData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\IuQuizSubmitRequest;
use App\Jobs\IU\CheckIfUserQuizExpiredJob;
use App\Jobs\IU\EvaluateQuizJob;
use App\Repositories\IU\IuQuizRepository;
use App\Transformers\IU\Cart\IuCourseModulesTransformer;
use App\Transformers\IU\CourseHierarchy\IuCourseLevelHierarchyTransformer;
use App\Transformers\IU\CourseHierarchy\IuCourseModuleHierarchyTransformer;
use App\Transformers\IU\CourseHierarchy\IuLessonHierarchyTransformer;
use App\Transformers\IU\Quiz\IuExamDetailsTransformer;
use App\Transformers\IU\Quiz\IuQuizPreviewTransformer;
use App\Transformers\IU\Quiz\IuUserQuizAttemptTransformer;
use App\Transformers\IU\Quiz\IuUserQuizTransformer;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuQuizController extends Controller
{
    private IuQuizRepository $iuQuizRepository;

    public function __construct(IuQuizRepository $iuQuizRepository)
    {
        $this->iuQuizRepository = $iuQuizRepository;
    }

    public function getLessonQuiz(Request $request, $courseId, $lessonId)
    {
        $userId = $request->user()->id;
        $lesson = $this->iuQuizRepository->getLessonData($courseId, $lessonId);
        if (! $lesson) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($userId, $lessonId, QuizData::ENTITY_LESSON);

        //Quiz successfully completed
        if ($userQuiz && $userQuiz->status == QuizData::STATUS_COMPLETED && $userQuiz->score >= QuizData::DEFAULT_PASSING_SCORE) {
            return response()->json(['errors' => Lang::get('iu.quiz.alreadyPassed')], 400);
        }

        //Quiz not taken or previously failed
        elseif (! $userQuiz || $userQuiz->status == QuizData::STATUS_COMPLETED) {
            $quiz = $this->iuQuizRepository->getQuizForEntity($lessonId, QuizData::ENTITY_LESSON);
            if (! $quiz) {
                return response()->json(['errors' => Lang::get('iu.quiz.noQuizFound')], 404);
            }

            $userQuiz = $this->iuQuizRepository->generateUserQuiz($userId, $quiz);
            CheckIfUserQuizExpiredJob::dispatch($userQuiz->id, $userQuiz->uuid)->delay(Carbon::now()->addSeconds($userQuiz->duration + 10))->onQueue('medium');
        } elseif ($userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['errors' => Lang::get('iu.quiz.previousAttemptBeingEvaluated')], 400);
        }
        //case of quiz still in progress is automatically handled

        $data = (object) [
            'quiz' => fractal($userQuiz, new IuUserQuizTransformer()),
            'entity' => fractal($lesson, new IuLessonHierarchyTransformer()),
        ];

        return response()->json($data, 200);
    }

    public function submitLessonQuiz(IuQuizSubmitRequest $request, $courseId, $lessonId)
    {
        $userId = $request->user()->id;
        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($userId, $lessonId, QuizData::ENTITY_LESSON);

        if (! $userQuiz || $userQuiz->status == QuizData::STATUS_COMPLETED) {
            return response()->json(['errors' => Lang::get('iu.quiz.notInitialized')], 400);
        }
        if ($userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['message' => Lang::get('iu.quiz.previousAttemptBeingEvaluated'), 'processing' => true], 202);
        }
        if ($userQuiz->status != QuizData::STATUS_IN_PROGRESS) {
            return response()->json(['errors' => Lang::get('iu.quiz.unknownStatus')], 400);
        }

        $userAnswers = $request->answers;
        if (! $this->iuQuizRepository->answerKeysMatch($userQuiz->answers, $userAnswers)) {
            return response()->json(['errors' => Lang::get('general.invalidData')], 422);
        }

        $userQuiz->status = QuizData::STATUS_SUBMITTED;
        $userQuiz->save();

        EvaluateQuizJob::dispatch($userQuiz->id, $userQuiz->uuid, $userAnswers)->onQueue('high');

        return response()->json(['message' => Lang::get('iu.quiz.successfullySubmitted')], 201);
    }

    public function getLessonQuizAttempt(Request $request, $courseId, $lessonId)
    {
        $userId = $request->user()->id;
        $lesson = $this->iuQuizRepository->getLessonData($courseId, $lessonId);
        if (! $lesson) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($userId, $lessonId, QuizData::ENTITY_LESSON);
        $quiz = $this->iuQuizRepository->getQuizForEntity($lessonId, QuizData::ENTITY_LESSON);

        if ($userQuiz && $userQuiz->status == QuizData::STATUS_IN_PROGRESS) {
            return response()->json(['errors' => Lang::get('iu.quiz.inProgress'), 'inProgress' => true], 400);
        } elseif ($userQuiz && $userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['errors' => Lang::get('iu.quiz.previousAttemptBeingEvaluated'), 'processing' => true], 400);
        }

        $data = (object) [
            'previousAttempt' => $userQuiz ? fractal($userQuiz, new IuUserQuizAttemptTransformer()) : null,
            'entity' => fractal($lesson, new IuLessonHierarchyTransformer()),
            'quizPreview' => fractal($quiz, new IuQuizPreviewTransformer()),
        ];

        return response()->json($data, 200);
    }

    public function getCourseModuleQuiz(Request $request, $courseId, $courseModuleId)
    {
        $userId = $request->user()->id;
        $courseModule = $this->iuQuizRepository->getCourseModuleData($courseId, $courseModuleId);
        if (! $courseModule) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($userId, $courseModuleId, QuizData::ENTITY_COURSE_MODULE);

        //Quiz successfully completed
        if ($this->userSuccessfullyCompletedQuiz($userQuiz)) {
            return response()->json(['errors' => Lang::get('iu.quiz.alreadyPassed')], 400);
        }

        //Quiz not taken or previously failed
        elseif ($this->userFailedQuiz($userQuiz)) {
            $quiz = $this->iuQuizRepository->getQuizForEntity($courseModuleId, QuizData::ENTITY_COURSE_MODULE);
            if (! $quiz) {
                return response()->json(['errors' => Lang::get('iu.quiz.noQuizFound')], 404);
            }

            $userQuiz = $this->iuQuizRepository->generateUserQuiz($userId, $quiz);
            CheckIfUserQuizExpiredJob::dispatch($userQuiz->id, $userQuiz->uuid)->delay(Carbon::now()->addSeconds($userQuiz->duration + 10))->onQueue('medium');
        } elseif ($userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['errors' => Lang::get('iu.quiz.previousAttemptBeingEvaluated')], 400);
        }
        //case of quiz still in progress is automatically handled

        $data = (object) [
            'quiz' => fractal($userQuiz, new IuUserQuizTransformer()),
            'entity' => fractal($courseModule, new IuCourseModuleHierarchyTransformer()),
        ];

        return response()->json($data, 200);
    }

    public function submitCourseModuleQuiz(IuQuizSubmitRequest $request, $courseId, $courseModuleId)
    {
        $userId = $request->user()->id;
        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($userId, $courseModuleId, QuizData::ENTITY_COURSE_MODULE);

        if (! $userQuiz || $userQuiz->status == QuizData::STATUS_COMPLETED) {
            return response()->json(['errors' => Lang::get('iu.quiz.notInitialized')], 400);
        }
        if ($userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['message' => Lang::get('iu.quiz.previousAttemptBeingEvaluated'), 'processing' => true], 202);
        }
        if ($userQuiz->status != QuizData::STATUS_IN_PROGRESS) {
            return response()->json(['errors' => Lang::get('iu.quiz.unknownStatus')], 400);
        }

        $userAnswers = $request->answers;
        if (! $this->iuQuizRepository->answerKeysMatch($userQuiz->answers, $userAnswers)) {
            return response()->json(['errors' => Lang::get('general.invalidData')], 422);
        }

        $userQuiz->status = QuizData::STATUS_SUBMITTED;
        $userQuiz->save();

        EvaluateQuizJob::dispatch($userQuiz->id, $userQuiz->uuid, $userAnswers)->onQueue('high');

        return response()->json(['message' => Lang::get('iu.quiz.successfullySubmitted')], 201);
    }

    public function getCourseModuleQuizAttempt(Request $request, $courseId, $courseModuleId)
    {
        $user = $request->user();
        $courseModule = $this->iuQuizRepository->getCourseModuleData($courseId, $courseModuleId);
        if (! $courseModule) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($user->id, $courseModuleId, QuizData::ENTITY_COURSE_MODULE);
        $quiz = $this->iuQuizRepository->getQuizForEntity($courseModuleId, QuizData::ENTITY_COURSE_MODULE);

        if ($userQuiz && $userQuiz->status == QuizData::STATUS_IN_PROGRESS) {
            return response()->json(['errors' => Lang::get('iu.quiz.inProgress'), 'inProgress' => true], 400);
        } elseif ($userQuiz && $userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['errors' => Lang::get('iu.quiz.previousAttemptBeingEvaluated'), 'processing' => true], 400);
        }

        $userCanAccessExam = $quiz->price ? $this->iuQuizRepository->userCanAccessExam($user->id, $quiz->id) : true;
        $userExamAttemptsLeft = ($quiz->price && $userCanAccessExam) ? $this->iuQuizRepository->userExamAttemptsLeft($user->id, $quiz->id)->attempts_left : 0;
        $data = (object) [
            'previousAttempt' => $userQuiz ? fractal($userQuiz, new IuUserQuizAttemptTransformer()) : null,
            'entity' => fractal($courseModule, new IuCourseModuleHierarchyTransformer()),
            'quizPreview' => fractal($quiz, new IuQuizPreviewTransformer()),
            'examDetails' => fractal($quiz, new IuExamDetailsTransformer($userCanAccessExam, $userExamAttemptsLeft)),
        ];

        return response()->json($data, 200);
    }

    public function getCourseModuleQuizAccess(Request $request, $courseId, $courseModuleId)
    {
        $userId = $request->user()->id;
        $courseModule = $this->iuQuizRepository->getCourseModuleData($courseId, $courseModuleId);
        $data = $this->iuQuizRepository->getCourseModuleQuizAccess($userId, $courseId, $courseModule->course_level_id);

        $fractal = fractal($data, new IuCourseModulesTransformer());

        return response()->json($fractal, 200);
    }

    public function getCourseLevelQuiz(Request $request, $courseId, $courseLevelId)
    {
        $userId = $request->user()->id;
        $courseLevel = $this->iuQuizRepository->getCourseLevelData($courseId, $courseLevelId);
        if (! $courseLevel) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($userId, $courseLevelId, QuizData::ENTITY_COURSE_LEVEL);
        //Quiz successfully completed
        if ($this->userSuccessfullyCompletedQuiz($userQuiz)) {
            return response()->json(['errors' => Lang::get('iu.quiz.alreadyPassed')], 400);
        }

        //Quiz not taken or previously failed
        elseif ($this->userFailedQuiz($userQuiz)) {
            $quiz = $this->iuQuizRepository->getQuizForEntity($courseLevelId, QuizData::ENTITY_COURSE_LEVEL);
            if (! $quiz) {
                return response()->json(['errors' => Lang::get('iu.quiz.noQuizFound')], 404);
            }

            $userQuiz = $this->iuQuizRepository->generateUserQuiz($userId, $quiz);
            CheckIfUserQuizExpiredJob::dispatch($userQuiz->id, $userQuiz->uuid)->delay(Carbon::now()->addSeconds($userQuiz->duration + 10))->onQueue('medium');
        } elseif ($userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['errors' => Lang::get('iu.quiz.previousAttemptBeingEvaluated')], 400);
        }
        //case of quiz still in progress is automatically handled

        $data = (object) [
            'quiz' => fractal($userQuiz, new IuUserQuizTransformer()),
            'entity' => fractal($courseLevel, new IuCourseLevelHierarchyTransformer()),
        ];

        return response()->json($data, 200);
    }

    public function submitCourseLevelQuiz(IuQuizSubmitRequest $request, $courseId, $courseLevelId)
    {
        $userId = $request->user()->id;
        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($userId, $courseLevelId, QuizData::ENTITY_COURSE_LEVEL);

        if (! $userQuiz || $userQuiz->status == QuizData::STATUS_COMPLETED) {
            return response()->json(['errors' => Lang::get('iu.quiz.notInitialized')], 400);
        }
        if ($userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['message' => Lang::get('iu.quiz.previousAttemptBeingEvaluated'), 'processing' => true], 202);
        }
        if ($userQuiz->status != QuizData::STATUS_IN_PROGRESS) {
            return response()->json(['errors' => Lang::get('iu.quiz.unknownStatus')], 400);
        }

        $userAnswers = $request->answers;
        if (! $this->iuQuizRepository->answerKeysMatch($userQuiz->answers, $userAnswers)) {
            return response()->json(['errors' => Lang::get('general.invalidData')], 422);
        }

        $userQuiz->status = QuizData::STATUS_SUBMITTED;
        $userQuiz->save();

        EvaluateQuizJob::dispatch($userQuiz->id, $userQuiz->uuid, $userAnswers)->onQueue('high');

        return response()->json(['message' => Lang::get('iu.quiz.successfullySubmitted')], 201);
    }

    public function getCourseLevelQuizAttempt(Request $request, $courseId, $courseLevelId)
    {
        $user = $request->user();
        $courseLevel = $this->iuQuizRepository->getCourseLevelData($courseId, $courseLevelId);

        if (! $courseLevel) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $userQuiz = $this->iuQuizRepository->getUserQuizForEntity($user->id, $courseLevelId, QuizData::ENTITY_COURSE_LEVEL);
        $quiz = $this->iuQuizRepository->getQuizForEntity($courseLevelId, QuizData::ENTITY_COURSE_LEVEL);

        if ($userQuiz && $userQuiz->status == QuizData::STATUS_IN_PROGRESS) {
            return response()->json(['errors' => Lang::get('iu.quiz.inProgress'), 'inProgress' => true], 400);
        } elseif ($userQuiz && $userQuiz->status == QuizData::STATUS_SUBMITTED) {
            return response()->json(['errors' => Lang::get('iu.quiz.previousAttemptBeingEvaluated'), 'processing' => true], 400);
        }

        $userCanAccessExam = $quiz->price ? $this->iuQuizRepository->userCanAccessExam($user->id, $quiz->id) : true;
        $userExamAttemptsLeft = ($quiz->price && $userCanAccessExam) ? $this->iuQuizRepository->userExamAttemptsLeft($user->id, $quiz->id)->attempts_left : 0;
        $data = (object) [
            'previousAttempt' => $userQuiz ? fractal($userQuiz, new IuUserQuizAttemptTransformer()) : null,
            'entity' => fractal($courseLevel, new IuCourseLevelHierarchyTransformer()),
            'quizPreview' => fractal($quiz, new IuQuizPreviewTransformer()),
            'examDetails' => fractal($quiz, new IuExamDetailsTransformer($userCanAccessExam, $userExamAttemptsLeft)),
        ];

        return response()->json($data, 200);
    }

    private function userSuccessfullyCompletedQuiz($userQuiz): bool
    {
        return $userQuiz && $userQuiz->status == QuizData::STATUS_COMPLETED && $userQuiz->score >= QuizData::DEFAULT_PASSING_SCORE;
    }

    private function userFailedQuiz($userQuiz): bool
    {
        return ! $userQuiz || $userQuiz->status == QuizData::STATUS_COMPLETED;
    }
}
