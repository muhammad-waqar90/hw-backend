<?php

namespace App\Http\Controllers\IU;

use App\DataObject\Tickets\TicketMessageTypeData;
use App\DataObject\Tickets\TicketStatusData;
use App\DataObject\Tickets\TicketSubjectData;
use App\Events\Notifications\Tickets\IuTicketReplied;
use App\Events\Notifications\Tickets\IuTicketResolved;
use App\Http\Controllers\Controller;
use App\Http\Requests\IU\CreateIuTicketRequest;
use App\Http\Requests\IU\IuReplyToTicketRequest;
use App\Mail\IU\Ticket\IuTicketCreatedEmail;
use App\Repositories\TicketRepository;
use App\Transformers\IU\IuMyTicketListTransformer;
use App\Transformers\IU\IuTicketMessageTransformer;
use App\Transformers\IU\IuTicketTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class IuTicketController extends Controller
{
    private TicketRepository $ticketRepository;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function getTicketSubjectList()
    {
        $data = $this->ticketRepository->getFullTicketSubjectList();

        return response()->json($data, 200);
    }

    public function getTicketSubject($id)
    {
        $data = $this->ticketRepository->getTicketSubject($id);
        if (! $data) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        return response()->json($data, 200);
    }

    public function submitTicket(CreateIuTicketRequest $request)
    {
        $userId = $request->user()->id;
        DB::beginTransaction();
        try {
            if ($request->subjectId == 0) {
                $ticketSubject = (object) TicketSubjectData::OTHER;
            } else {
                $ticketSubject = $this->ticketRepository->getTicketSubject($request->subjectId);
            }

            if (! $ticketSubject) {
                return response()->json(['errors' => Lang::get('tickets.invalidSubjectId')], 422);
            }

            $ticket = $this->ticketRepository->createIuTicket($ticketSubject->ticket_category_id, $userId, $ticketSubject->name, $request->log);
            $message = $this->ticketRepository->createMessage(
                $userId,
                $ticket->id,
                $request->message,
                TicketMessageTypeData::USER_MESSAGE
            );

            if ($request->assets) {
                $this->ticketRepository->handleTicketAssets($userId, $request->assets, $ticket->id, TicketMessageTypeData::USER_ASSET_MESSAGE);
            }

            DB::commit();

            Mail::to($request->user()->userProfile->email)
                ->queue(new IuTicketCreatedEmail($request->user(), $message->message, $ticketSubject->name, $ticket->id));

            return response()->json([
                'message' => Lang::get('tickets.successfullySubmittedTicket'),
                'data' => $ticket,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: IuTicketController@submitTicket', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getMyTicketList(Request $request)
    {
        $userId = $request->user()->id;
        $searchStatus = $this->ticketRepository->parseSearchStatus($request->status);
        if (! $searchStatus) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        $data = $this->ticketRepository->getMyTicketList($userId, $searchStatus, $request->subject)
            ->appends([
                'subject' => $request->subject,
                'status' => $request->status,
            ]);

        $fractal = fractal($data->getCollection(), new IuMyTicketListTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getTicket($id, Request $request)
    {
        $ticket = $request->page == null ? $this->ticketRepository->getTicketDetails($id) : null;
        if ($request->page == null && ! $ticket) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $messages = $this->ticketRepository->getTicketMessagesList($id);

        $fractal = fractal($messages->getCollection(), new IuTicketMessageTransformer());
        $messages->setCollection(collect($fractal));

        $data = (object) [
            'messages' => $messages,
        ];
        if ($request->page == null) {
            $data->ticket = fractal($ticket, new IuTicketTransformer());
        }

        if ($ticket) {
            $this->ticketRepository->ticketSeenByUser($ticket->id, true);
        }

        return response()->json($data, 200);
    }

    public function replyToTicket($id, IuReplyToTicketRequest $request)
    {
        $userId = $request->user()->id;
        $ticket = $this->ticketRepository->getTicket($id);

        DB::beginTransaction();
        try {
            $message = $this->ticketRepository->createMessage(
                $userId,
                $id,
                $request->message,
                TicketMessageTypeData::USER_MESSAGE
            );

            $ticketMessages = [$message];

            if ($request->assets) {
                $assets = $this->ticketRepository->handleTicketAssets($userId, $request->assets, $ticket->id, TicketMessageTypeData::USER_ASSET_MESSAGE);
                $ticketMessages = [...$ticketMessages, ...$assets];
            }

            foreach ($ticketMessages as $key => $message) {
                $ticketMessages[$key] = fractal($message, new IuTicketMessageTransformer());
            }

            $ticket->seen_by_admin = false;
            $ticket->save();
            $data = (object) [
                'message' => $ticketMessages,
            ];

            DB::commit();

            if ($ticket->admin_id) {
                IuTicketReplied::dispatch($ticket->id, $ticket->admin_id, $request->message);
            }

            return response()->json(['message' => Lang::get('tickets.successfullyRepliedToTicket'), 'data' => $data], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function resolveTicket($id, Request $request)
    {
        $userId = $request->user()->id;
        $userName = $request->user()->name;
        $ticket = $this->ticketRepository->getTicket($id);

        DB::beginTransaction();
        try {
            $message = $this->ticketRepository->createMessage(
                $userId,
                $id,
                '"'.$userName.'" marked the ticket as resolved',
                TicketMessageTypeData::SYSTEM_MESSAGE
            );
            $ticket->ticket_status_id = TicketStatusData::RESOLVED;
            $ticket->seen_by_admin = false;
            $ticket->save();

            DB::commit();

            if ($ticket->admin_id) {
                IuTicketResolved::dispatch($ticket->id, $ticket->admin_id, $userName);
            }

            return response()->json(['message' => Lang::get('tickets.successfullyResolvedTicket'), 'data' => $message], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function reopenTicket($id, Request $request)
    {
        $userId = $request->user()->id;
        $userName = $request->user()->name;
        $ticket = $this->ticketRepository->getTicket($id);

        if ($ticket->ticket_status_id != TicketStatusData::RESOLVED) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        DB::beginTransaction();
        try {
            $message = $this->ticketRepository->createMessage(
                $userId,
                $id,
                '"'.$userName.'" marked the ticket as reopened',
                TicketMessageTypeData::SYSTEM_MESSAGE
            );
            $ticket->ticket_status_id = TicketStatusData::REOPENED;
            $ticket->admin_id = null;
            $ticket->seen_by_admin = false;
            $ticket->save();

            DB::commit();

            return response()->json(['message' => Lang::get('tickets.successfullyReopenedTicket'), 'data' => $message], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
