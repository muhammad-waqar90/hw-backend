<?php

namespace App\Jobs\IU;

use App\DataObject\QuizData;
use App\Models\VerifyUser;
use App\Repositories\IU\IuQuizRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckIfUserQuizExpiredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var VerifyUser
     */
    private $userQuizId;
    private $userQuizUuid;

    /**
     * Create a new job instance.
     *
     * @param $userQuizId
     * @param $userQuizUuid
     */
    public function __construct($userQuizId, $userQuizUuid)
    {
        $this->userQuizId = $userQuizId;
        $this->userQuizUuid = $userQuizUuid;
    }

    /**
     * Execute the job.
     *
     * @param IuQuizRepository $iuQuizRepository
     * @return void
     */
    public function handle(IuQuizRepository $iuQuizRepository)
    {
        $userQuiz = $iuQuizRepository->getUserQuiz($this->userQuizId, $this->userQuizUuid);
        if(!$userQuiz)
            return;
        if($userQuiz->status === QuizData::STATUS_IN_PROGRESS)
            $iuQuizRepository->invalidateUserQuiz($this->userQuizId);
    }
}
