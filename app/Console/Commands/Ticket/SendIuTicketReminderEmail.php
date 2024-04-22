<?php

namespace App\Console\Commands\Ticket;

use App\DataObject\Tickets\TicketStatusData;
use App\Models\Ticket;
use App\Mail\IU\Ticket\IuTicketReminderEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendIuTicketReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:iuTicketReminderEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send IU reminder email to reply to ticket';

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

        foreach($ticketsNotSeenByUser as $ticket) {
            Mail::to($ticket->user->userProfile->email)->queue(new IuTicketReminderEmail($ticket->user, $ticket->subject, $ticket->id));
        }

        $this->info('Sending IU ticket reminder email');
    }

    public function getStaleTickets()
    {
        return $this->ticket
            ->whereNotNull('user_id')
            ->where('ticket_status_id', TicketStatusData::IN_PROGRESS)
            ->where('seen_by_user', 0)
            ->whereHas('hasAdminReply')
            ->where('updated_at', '>', Carbon::now()->subHours(config('ticket.iu_ticket_not_replied_reminder_email')))
            ->where('updated_at', '<', Carbon::now()->subHours(config('ticket.iu_ticket_not_replied_reminder_email') - 1))
            ->with('user', function ($query) {
                $query->with('userProfile');
            })
            ->get();
    }
}
