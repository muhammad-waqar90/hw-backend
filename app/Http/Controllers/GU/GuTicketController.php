<?php


namespace App\Http\Controllers\GU;


use App\DataObject\Tickets\TicketMessageTypeData;
use App\DataObject\Tickets\TicketSubjectData;
use App\Http\Controllers\Controller;
use App\Http\Requests\GU\CreateGuTicketRequest;
use App\Mail\GuestTicketCreatedEmail;
use App\Repositories\TicketRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GuTicketController extends Controller
{

    private TicketRepository $ticketRepository;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function getTicketSubjectList()
    {
        $data = $this->ticketRepository->getFullTicketSubjectList(true);
        return response()->json($data, 200);
    }

    public function getTicketSubject($id)
    {
        $data = $this->ticketRepository->getTicketSubject($id, true);
        if(!$data)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        return response()->json($data, 200);
    }

    public function submitTicket(CreateGuTicketRequest $request)
    {
        DB::beginTransaction();
        try {
            if($request->subjectId == 0)
                $ticketSubject = (object) TicketSubjectData::OTHER;
            else
                $ticketSubject = $this->ticketRepository->getTicketSubject($request->subjectId, true);

            if(!$ticketSubject)
                return response()->json(['errors' => Lang::get('tickets.invalidSubjectId')], 422);
            $ticket = $this->ticketRepository->createGuestTicket($ticketSubject->ticket_category_id, $request->email, $ticketSubject->name, $request->log);
            $message = $this->ticketRepository->createMessage(null, $ticket->id, $request->message, TicketMessageTypeData::USER_MESSAGE);

            if($request->assets)
                $this->ticketRepository->handleTicketAssets(null, $request->assets, $ticket->id, TicketMessageTypeData::USER_ASSET_MESSAGE);

            Mail::to($request->email)->queue(new GuestTicketCreatedEmail(null, $message->message, $ticketSubject->name));

            DB::commit();

            return response()->json(['message' => Lang::get('tickets.successfullySubmittedTicket')], 200);
        } catch(\Exception $e) {
            DB::rollback();

            Log::error('Exception: GuTicketController@submitTicket', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }
}
