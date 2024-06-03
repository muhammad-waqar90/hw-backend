<?php

namespace App\Http\Middleware\IU;

use App\DataObject\Tickets\TicketCategoryData;
use App\Repositories\TicketRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuCanAccessTicket
{
    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * IuCanReplyToTicket constructor.
     */
    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ticket = $this->ticketRepository->getTicket($request->id);
        if (! $ticket) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
        if ($request->user()->id != $ticket->user_id || $ticket->ticket_category_id == TicketCategoryData::LESSON_QA) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        return $next($request);
    }
}
