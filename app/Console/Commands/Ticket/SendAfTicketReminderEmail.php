<?php

namespace App\Console\Commands\Ticket;

use App\DataObject\PermissionData;
use App\DataObject\RoleData;
use App\DataObject\Tickets\TicketCategoryData;
use App\DataObject\Tickets\TicketStatusData;
use App\Events\Notifications\Tickets\IuTicketNotClaimed;
use App\Mail\IU\Ticket\IuTicketNotClaimedReminderEmail;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAfTicketReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:afTicketReminderEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send AF ticket reminder email';

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Ticket $ticket, User $user)
    {
        parent::__construct();
        $this->ticket = $ticket;
        $this->user = $user;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ticketsNotClaimed = $this->getUnclaimedTickets();
        if ($ticketsNotClaimed->isEmpty()) {
            return;
        }

        $admins = $this->getTicketTeamLeaders();
        if ($admins->isEmpty()) {
            return;
        }

        $this->sendNotificationAndEmail($ticketsNotClaimed, $admins);
        $this->info('Sending AF ticket reminder email');
    }

    public function getUnclaimedTickets()
    {
        return $this->ticket
            ->where('ticket_status_id', TicketStatusData::UNCLAIMED)
            ->where('updated_at', '>', Carbon::now()->subHours(config('ticket.af_ticket_claim_reminder_email')))
            ->where('updated_at', '<', Carbon::now()->subHours(config('ticket.af_ticket_claim_reminder_email') - 1))
            ->get();
    }

    public function getTicketTeamLeaders()
    {
        return $this->user
            ->where('role_id', RoleData::ADMIN)
            ->where('is_enabled', 1)
            ->with('adminProfile')
            ->whereHas('permGroups', function ($query) {
                $query->whereHas('permissions', function ($query) {
                    $query->where('permission_id', PermissionData::TICKET_TEAM_LEADERSHIP);
                });
            })
            ->get();
    }

    public function sendNotificationAndEmail($ticketsNotClaimed, $admins)
    {
        foreach ($ticketsNotClaimed as $ticket) {
            if ($ticket->user_id && $ticket->ticket_category_id != TicketCategoryData::LESSON_QA) {
                IuTicketNotClaimed::dispatch($ticket->id, $ticket->user_id, $ticket->subject);
            }

            foreach ($admins as $admin) {
                Mail::to($admin->adminProfile->email)->queue(new IuTicketNotClaimedReminderEmail($admin, $ticket->subject, $ticket->id));
            }
        }
    }
}
