<?php

namespace App\Repositories\IU;

use App\DataObject\QuizData;
use App\Exceptions\Quizzes\InvalidAnswerDataException;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseModule;
use App\Models\ExamAccess;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\UserQuiz;
use Carbon\Carbon;
use Illuminate\Support\Str;

class IuQuizRepository
{
    private Quiz $quiz;
    private UserQuiz $userQuiz;
    private Lesson $lesson;
    private CourseModule $courseModule;
    private CourseLevel $courseLevel;
    private ExamAccess $examAccess;
    private Course $course;
    private IuQuizItemRepository $iuQuizItemRepository;

    public function __construct(Quiz $quiz, UserQuiz $userQuiz, Lesson $lesson, CourseModule $courseModule, CourseLevel $courseLevel,
        ExamAccess $examAccess, Course $course, IuQuizItemRepository $iuQuizItemRepository)
    {
        $this->quiz = $quiz;
        $this->userQuiz = $userQuiz;
        $this->lesson = $lesson;
        $this->courseModule = $courseModule;
        $this->courseLevel = $courseLevel;
        $this->examAccess = $examAccess;
        $this->course = $course;
        $this->iuQuizItemRepository = $iuQuizItemRepository;
    }

    public function getQuizForEntity($entityId, $entityType)
    {
        return $this->quiz->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->first();
    }

    public function getEntityHasQuiz($entityId, $entityType)
    {
        return $this->quiz->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->exists();
    }

    public function getQuiz($id)
    {
        return $this->quiz
            ->where('id', $id)
            ->first();
    }

    public function getUserQuiz($id, $uuid = null)
    {
        $userQuiz = $this->userQuiz->where('id', $id);
        if($uuid)
            $userQuiz->where('uuid', $uuid);
        return $userQuiz->first();
    }

    public function getUserQuizForEntity($userId, $entityId, $entityType)
    {
        return $this->userQuiz->latest('id')
            ->where('user_id', $userId)
            ->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->first();
    }

    public function generateUserQuiz($userId, Quiz $quiz)
    {
        $generatedQuestionsAnswers = $this->iuQuizItemRepository->getQuestionsAnswersForQuiz($userId, $quiz);

        return $this->userQuiz->create([
            'user_id' => $userId,
            'entity_id' => $quiz->entity_id,
            'entity_type' => $quiz->entity_type,
            'uuid' => Str::orderedUuid()->toString(),
            'questions' => json_encode($generatedQuestionsAnswers['questions']),
            'answers'   => json_encode($generatedQuestionsAnswers['answers']),
            'duration'  => $quiz->duration,
            'status'    => QuizData::STATUS_IN_PROGRESS,
            'num_of_questions' => $quiz->num_of_questions,
            'score' => 0,
            'user_answers' => NULL,
            'started_at' => Carbon::now()->toDateTimeString()
        ]);

    }

    public function invalidateUserQuiz($id)
    {
        $this->userQuiz->where('id', $id)
        ->update([
            'status'    => QuizData::STATUS_COMPLETED,
            'score' => 0,
            'user_answers' => NULL
        ]);
    }

    public function answerKeysMatch($answers1, $answers2)
    {
        sort($answers1);
        sort($answers2);
        if(array_keys($answers1) === array_keys($answers2))
            return true;
        return false;
    }

    public function getQuizScore($userQuiz, $userAnswers)
    {
        $quizAnswers = (array) $userQuiz->answers;

        $score = 0;
        foreach($quizAnswers as $key => $quizAnswer) {
            $score += $this->scoreAnswer($quizAnswer, $userAnswers[$key]);
        }

        // calculate score in percentages rounding up
        return (int) ceil(($score / count($quizAnswers)) * 100);
    }

    public function getLessonData($courseId, $id)
    {
        return $this->lesson->where('course_id', $courseId)
            ->where('id', $id)
            ->with('courseModule', function ($query) {
                $query->with('courseLevel', function ($query) {
                    $query->with('course');
                });
            })
            ->first();
    }

    public function getCourseModuleData($courseId, $id)
    {
        return $this->courseModule->where('course_id', $courseId)
            ->where('id', $id)->with('courseLevel', function ($query) {
                $query->with('course');
            })
            ->first();
    }

    public function getCourseLevelData($courseId, $id)
    {
        return $this->courseLevel->where('course_id', $courseId)
            ->where('id', $id)->with('course')
            ->first();
    }

    public function userCanAccessExam($userId, $quizId)
    {
        return $this->examAccess->where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->where('attempts_left', '>', 0)
            ->exists();
    }

    public function userExamAttemptsLeft($userId, $quizId)
    {
        return $this->examAccess->select('attempts_left')->latest('id')
            ->where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->first();
    }

    public function assignExamToUser($userId, $quizId)
    {
        return $this->examAccess->create(
            [
                'user_id'      => $userId,
                'quiz_id'      => $quizId,
                'attempts_left' => QuizData::QUIZ_EXAM_ALLOWED_ATTEMPTS
            ]
        );
    }

    public function updateExamAccessAttemptsLeft($userId, $quizId)
    {
        $examAccess = self::getLatestExamAccess($userId, $quizId);
        
        return $this->examAccess->where('id', $examAccess->id)
            ->decrement('attempts_left');
    }

    public function getLatestExamAccess($userId, $quizId)
    {
        return $this->examAccess->latest('id')
            ->where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->first();
    }

    public function getCourseModuleQuizAccess($userId, $courseId, $courseLevelId)
    {
        return $this->course
            ->select('id', 'name', 'img')
            ->where('id', $courseId)
            ->with('courseLevel', function ($query) use ($courseLevelId, $userId) {
                $query->where('id', $courseLevelId)
                    ->with('courseModules', function ($query) use ($courseLevelId, $userId) {
                        $query->select('qz.id as quizId', 'course_modules.id as courseModuleId', 'course_modules.name as name', 'qz.price as price',
                            'ea.id as purchased', 'course_modules.course_level_id')
                            ->join('quizzes as qz', function ($query) {
                                $query->on('qz.entity_id', '=', 'course_modules.id')
                                    ->where('qz.entity_type', QuizData::ENTITY_COURSE_MODULE)
                                    ->where('price', '!=', null);
                            })
                            ->leftJoin('exam_accesses as ea', function ($query) use ($userId) {
                                $query->on('ea.quiz_id', '=', 'qz.id')
                                    ->where('user_id', $userId)
                                    ->where('attempts_left', '>', 0);
                            });
                        });
            })
            ->first();
    }

    private function scoreAnswer($quizAnswer, $userAnswer)
    {
        if($userAnswer['answerId'] === null)
            $userAnswer['answerId'] = "";

        if(gettype($quizAnswer['answerId']) !== gettype($userAnswer['answerId']))
            throw new InvalidAnswerDataException();

        // calculate score for specific type of question
        if($quizAnswer['type']  === QuizData::QUESTION_MCQ_SINGLE)
            return $this->scoreAnswerMcqSingle($quizAnswer['answerId'], $userAnswer['answerId']);
        if($quizAnswer['type']  === QuizData::QUESTION_MCQ_MULTIPLE)
            return $this->scoreAnswerMcqMultiple($quizAnswer['answerId'], $userAnswer['answerId'], $quizAnswer['maxChoices']);
        if($quizAnswer['type']  === QuizData::QUESTION_MISSING_WORD)
            return $this->scoreAnswerMissingWord($quizAnswer['answerId'], $userAnswer['answerId']);
        if($quizAnswer['type']  === QuizData::QUESTION_LINKING)
            return $this->scoreAnswerLinking($quizAnswer['answerId'], $userAnswer['answerId']);

        return 0;
    }

    private function scoreAnswerMcqSingle($quizAnswer, $userAnswer)
    {
        if($quizAnswer == $userAnswer)
            return 1;
        return 0;
    }

    private function scoreAnswerMcqMultiple($quizAnswer, $userAnswer, $maxChoices)
    {
        if(count($userAnswer) > $maxChoices)
            throw new InvalidAnswerDataException();

        $score = 0;
        foreach($userAnswer as $answer)
            $score += in_array($answer, $quizAnswer) ? 1 : 0;

        return $score / $maxChoices;
    }

    private function scoreAnswerMissingWord($quizAnswer, $userAnswer)
    {
        if($quizAnswer == $userAnswer)
            return 1;
        return 0;
    }

    private function scoreAnswerLinking($linkingAnswers, $userLinkingAnswers)
    {
        if(count($userLinkingAnswers) > count($linkingAnswers))
            throw new InvalidAnswerDataException();

        $score = 0;
        foreach($linkingAnswers as $key => $answer)
            $score += array_key_exists($key, $userLinkingAnswers) && $answer == $userLinkingAnswers[$key] ? 1 : 0;

        return $score / count($linkingAnswers);
    }

    public function revokeExamAccessFromUser($userId, $id)
    {
        return $this->examAccess
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update([
                'attempts_left' => 0,
            ]);
    }

    public function invalidateUserRefundedExam($userId, $entityId)
    {
        return $this->userQuiz->select('user_quizzes.*')
            ->join('quizzes as qz', function ($query) {
                $query->on('qz.entity_id', 'user_quizzes.entity_id')
                    ->on('qz.entity_type','user_quizzes.entity_type')
                    ->join('exam_accesses as ea', function ($query) {
                        $query->on('ea.quiz_id', 'qz.id');
                    });
            })
            ->where('ea.id',$entityId)
            ->where('user_quizzes.user_id', $userId)
            ->where('status', QuizData::STATUS_IN_PROGRESS)
            ->update([
                'status' => QuizData::STATUS_COMPLETED,
            ]);
    }

}
