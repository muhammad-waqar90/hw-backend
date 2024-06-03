<?php

namespace App\Http\Controllers\IU;

use App\DataObject\Tickets\TicketCategoryData;
use App\DataObject\Tickets\TicketMessageTypeData;
use App\DataObject\Tickets\TicketStatusData;
use App\Events\Notifications\Tickets\AfTicketReplied;
use App\Http\Controllers\Controller;
use App\Http\Requests\IU\CreateIuLessonQaRequest;
use App\Repositories\IU\IuLessonQaRepository;
use App\Repositories\LessonRepository;
use App\Repositories\TicketRepository;
use App\Transformers\IU\IuLessonQaListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuLessonQaController extends Controller
{
    private IuLessonQaRepository $iuLessonQaRepository;

    private LessonRepository $lessonRepository;

    private TicketRepository $ticketRepository;

    public function __construct(IuLessonQaRepository $iuLessonQaRepository, LessonRepository $lessonRepository,
        TicketRepository $ticketRepository)
    {
        $this->iuLessonQaRepository = $iuLessonQaRepository;
        $this->lessonRepository = $lessonRepository;
        $this->ticketRepository = $ticketRepository;
    }

    public function getMyLessonQaTicketList(Request $request, int $courseId, int $lessonId)
    {
        $userId = $request->user()->id;
        $lesson = $this->lessonRepository->get($courseId, $lessonId);
        if (! $lesson) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $data = $this->ticketRepository
            ->getLessonQaTicketsQuery($userId, $lessonId, [TicketStatusData::RESOLVED])
            ->simplePaginate(20);

        $fractal = fractal($data->getCollection(), new IuLessonQaListTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getMyLatestLessonQaTicket(Request $request, int $courseId, int $lessonId)
    {
        $userId = $request->user()->id;
        $lesson = $this->lessonRepository->get($courseId, $lessonId);
        if (! $lesson) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $searchStatuses = array_values(TicketStatusData::getConstants());
        $ticket = $this->ticketRepository
            ->getLessonQaTicketsQuery($userId, $lessonId, $searchStatuses)
            ->first();

        if (! $ticket) {
            return response()->json(['question' => null, 'answer' => null], 200);
        }

        $question = $ticket->ticketMessages[array_key_first($ticket->ticketMessages->toArray())]?->message;
        $answer = $ticket->ticket_status_id == TicketStatusData::RESOLVED ? $ticket->ticketMessages[array_key_last($ticket->ticketMessages->toArray())]?->message : null;

        return response()->json([
            'question' => $question ?: null,
            'answer' => $answer ?: null,
        ], 200);
    }

    public function createLessonQaTicket(CreateIuLessonQaRequest $request, int $courseId, int $lessonId)
    {
        $userId = $request->user()->id;

        $lesson = $this->lessonRepository->get($courseId, $lessonId);
        if (! $lesson) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        if ($this->ticketRepository->isTicketMessageExist($userId, $request->question, $lessonId)) {
            return response()->json(['errors' => Lang::get('tickets.questionAlreadyExist')], 422);
        }

        $ticket = $this->ticketRepository->createIuTicket(TicketCategoryData::LESSON_QA, $userId, $lesson->name, null);
        $this->ticketRepository->createMessage($userId, $ticket->id, $request->question, TicketMessageTypeData::USER_MESSAGE);
        $this->iuLessonQaRepository->createLessonQaTicket($lessonId, $ticket->id);

        // handle auto system reply
        $lessonFaq = $this->iuLessonQaRepository->getSystemIntelligentAnswer($lessonId, $request->question);
        if ($lessonFaq) {
            $this->ticketRepository->createMessage($userId, $ticket->id, $lessonFaq->answer, TicketMessageTypeData::SYSTEM_MESSAGE);
            $this->ticketRepository->markAsResolved($ticket->id);

            $iuTicketLinkIds = [
                'lessonId' => $lessonId,
                'courseId' => $courseId,
            ];

            AfTicketReplied::dispatch(
                $ticket->id,
                $ticket->user_id,
                $lessonFaq->answer,
                $lesson->name,
                $iuTicketLinkIds
            );
        }

        return response()->json([
            'message' => Lang::get('tickets.successfullySubmittedTicket'),
            'data' => ['question' => $request->question, 'answer' => $lessonFaq?->answer],
        ], 200);
    }
}
