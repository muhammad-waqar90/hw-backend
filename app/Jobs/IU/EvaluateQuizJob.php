<?php

namespace App\Jobs\IU;

use App\DataObject\QuizData;
use App\Exceptions\Quizzes\InvalidAnswerDataException;
use App\Repositories\IU\IuQuizRepository;
use App\Repositories\IuProgressRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EvaluateQuizJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userQuizId;
    private $userQuizUuid;
    private $userAnswers;

    /**
     * Create a new job instance.
     *
     * @param $userQuizId
     * @param $userQuizUuid
     * @param $userAnswers
     */
    public function __construct($userQuizId, $userQuizUuid, $userAnswers)
    {
        $this->userQuizId = $userQuizId;
        $this->userQuizUuid = $userQuizUuid;
        $this->userAnswers = $userAnswers;
    }

    /**
     * Execute the job.
     *
     * @param IuQuizRepository $iuQuizRepository
     * @param IuProgressRepository $iuProgressRepository
     * @return void
     */
    public function handle(IuQuizRepository $iuQuizRepository, IuProgressRepository $iuProgressRepository)
    {
        $userQuiz = $iuQuizRepository->getUserQuiz($this->userQuizId, $this->userQuizUuid);
        if(!$userQuiz || $userQuiz->status !== QuizData::STATUS_SUBMITTED)
            return;
        try {
            $score = $iuQuizRepository->getQuizScore($userQuiz, $this->userAnswers);

            $userQuiz->score = $score;
            $userQuiz->user_answers = $this->userAnswers;
            $userQuiz->status = QuizData::STATUS_COMPLETED;
            $userQuiz->save();

            $this->updateUserProgress($userQuiz, $iuProgressRepository);
            $this->updateExamAccessAttemptsLeft($userQuiz, $iuQuizRepository);

        } catch(InvalidAnswerDataException $e) {
            $iuQuizRepository->invalidateUserQuiz($this->userQuizId);
            $this->updateUserProgress($userQuiz, $iuProgressRepository);
            Log::error('Exception: EvaluateQuizJob@handle', [$e->getMessage()]);
        }
    }

    private function updateUserProgress($userQuiz, IuProgressRepository $iuProgressRepository)
    {
        if($userQuiz->entity_type == QuizData::ENTITY_LESSON)
            $iuProgressRepository->calculateLessonProgress($userQuiz->user_id, $userQuiz->entity_id, $userQuiz);
        if($userQuiz->entity_type == QuizData::ENTITY_COURSE_MODULE)
            $iuProgressRepository->calculateCourseModuleProgress($userQuiz->user_id, $userQuiz->entity_id, $userQuiz);
        if($userQuiz->entity_type == QuizData::ENTITY_COURSE_LEVEL)
            $iuProgressRepository->calculateCourseLevelProgress($userQuiz->user_id, $userQuiz->entity_id, $userQuiz);
    }

    private function updateExamAccessAttemptsLeft($userQuiz, IuQuizRepository $iuQuizRepository)
    {
        if($userQuiz->entity_type == QuizData::ENTITY_COURSE_MODULE || $userQuiz->entity_type == QuizData::ENTITY_COURSE_LEVEL) {
            $quiz = $iuQuizRepository->getQuizForEntity($userQuiz->entity_id, $userQuiz->entity_type);
            if($quiz && $quiz->price)
                $iuQuizRepository->updateExamAccessAttemptsLeft($userQuiz->user_id, $quiz->id);
        }
    }
}
