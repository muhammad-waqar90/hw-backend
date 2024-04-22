<?php

namespace App\Console\Commands\Ticket;

use App\DataObject\PermissionData;
use App\DataObject\RoleData;
use App\DataObject\Tickets\TicketStatusData;
use App\Mail\AF\Ticket\AfTicketOnHoldReminderEmail;
use App\Models\User;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * - get on hold tickets - on hold from last 5 days hourly bases
 * - send email notification to AF Lead - PermissionData::TICKET_TEAM_LEADERSHIP
 */
class SendAfTicketOnHoldReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:afTicketOnHoldReminderEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending AF ticket on hold reminder email to Head AF';

    /**
     * @var Ticket $ticket
     */
    protected $ticket;

    /**
     * @var User $user
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
     * @return int
     */
    public function handle()
    {
        $onHoldTickets = $this->getOnHoldTickets();
        if ($onHoldTickets->isEmpty())
            return;

        $admins = $this->getTicketTeamLeaders();
        if ($admins->isEmpty())
            return;

        $this->sendEmail($onHoldTickets, $admins);
        $this->info('Sending AF ticket on hold reminder email to Head AF');
    }

    public function getOnHoldTickets()
    {
        return $this->ticket
            ->where('ticket_status_id', TicketStatusData::ON_HOLD)
            ->where('updated_at', '>', Carbon::now()->subHours(config('ticket.af_ticket_on_hold_reminder_email')))
            ->where('updated_at', '<', Carbon::now()->subHours(config('ticket.af_ticket_on_hold_reminder_email') - 1))
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

    public function sendEmail($onHoldTickets, $admins)
    {
        foreach ($onHoldTickets as $ticket) {
            // TODO: can BCC all admins rather sending individual emails
            foreach ($admins as $admin) {
                Mail::to($admin->adminProfile->email)->queue(new AfTicketOnHoldReminderEmail($admin, $ticket->subject, $ticket->id));
            }
        }
    }
}
