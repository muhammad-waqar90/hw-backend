<?php

namespace App\Console\Commands\Ticket;

use App\DataObject\Tickets\TicketStatusData;
use App\Jobs\Ticket\SendIuTicketClosedEmailJob;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendIuTicketClosedEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:iuTicketClosedEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send IU ticket closed email';

    /**
     * @var Ticket $ticket
     */
    protected $ticket;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Ticket $ticket)
    {
        parent::__construct();
        $this->ticket = $ticket;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ticketsNotSeenByUser = $this->getStaleTickets();
        if($ticketsNotSeenByUser->isEmpty())
            return;

        foreach($ticketsNotSeenByUser as $ticket)
            SendIuTicketClosedEmailJob::dispatch($ticket)->onQueue('low');

        $this->info('Sending IU ticket closed email');
    }

    public function getStaleTickets()
    {
        return $this->ticket
            ->whereNotNull('user_id')
            ->where('ticket_status_id', TicketStatusData::IN_PROGRESS)
            ->where('seen_by_user', 0)
            ->whereHas('hasAdminReply')
            ->where('updated_at', '>', Carbon::now()->subHours(config('ticket.iu_ticket_closed_email')))
            ->where('updated_at', '<', Carbon::now()->subHours(config('ticket.iu_ticket_closed_email') - 1))
            ->with('user', function ($query) {
                $query->with('userProfile');
            })
            ->get();
    }
}
