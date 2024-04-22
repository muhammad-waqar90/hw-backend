<?php

namespace App\Repositories\IU;

use App\Repositories\AF\AfLessonFaqRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IuLessonQaRepository
{
    private AfLessonFaqRepository $afLessonFaqRepository;

    public function __construct(AfLessonFaqRepository $afLessonFaqRepository)
    {
        $this->afLessonFaqRepository = $afLessonFaqRepository;
    }

    public function createLessonQaTicket($lessonId, $ticketId)
    {
        DB::table('lesson_ticket')->insert(
            [
                'lesson_id'     =>  $lessonId,
                'ticket_id'     =>  $ticketId,
                'created_at'    =>  Carbon::now(),
                'updated_at'    =>  Carbon::now()
            ]
        );
    }

    public function getSystemIntelligentAnswer($lessonId, $question)
    {
        return $this->afLessonFaqRepository->getLessonFaqByLessonId($lessonId, $question);
    }
}
