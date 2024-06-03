<?php

namespace App\Http\Middleware\AF;

use App\DataObject\Tickets\TicketStatusData;
use App\Repositories\AF\AfTicketRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class AfCanReplyToTicket
{
    /**
     * @var AfTicketRepository
     */
    private $afTicketRepository;

    /**
     * AfCanReplyToTicket constructor.
     */
    public function __construct(AfTicketRepository $afTicketRepository)
    {
        $this->afTicketRepository = $afTicketRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ticket = $this->afTicketRepository->getTicket($request->id);
        if (! $ticket) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        if (! $this->canReply($request->user()->id, $ticket)) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        return $next($request);
    }

    /**
     * Admin who claimed the ticket, can reply to in_progress or on_hold tickets
     */
    public function canReply($userId, $ticket)
    {
        return $userId == $ticket->admin_id &&
            count(array_intersect([$ticket->ticket_status_id], [TicketStatusData::IN_PROGRESS, TicketStatusData::ON_HOLD]));
    }
}
