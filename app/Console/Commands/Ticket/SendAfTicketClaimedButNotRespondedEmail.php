<?php

namespace App\Console\Commands\Ticket;

use App\DataObject\PermissionData;
use App\DataObject\RoleData;
use App\DataObject\Tickets\TicketStatusData;
use App\Mail\AF\Ticket\AfTicketClaimedButNotRespondedEmail;
use App\Models\User;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAfTicketClaimedButNotRespondedEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:afTicketClaimedButNotRespondedEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send AF ticket claimed but not responded email';

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
        $ticketsClaimedButNotResponded = $this->getClaimedButNotRespondedTickets();
        if($ticketsClaimedButNotResponded->isEmpty())
            return;

        $admins = $this->getTicketTeamLeaders();
        if($admins->isEmpty())
            return;

        $this->sendEmail($ticketsClaimedButNotResponded, $admins);
        $this->info('Sending AF ticket claimed but not responded email to Head AF');
    }

    public function getClaimedButNotRespondedTickets()
    {
        return $this->ticket
            ->where('ticket_status_id', TicketStatusData::IN_PROGRESS)
            ->whereDoesntHave('hasAdminReply')
            ->where('updated_at', '>', Carbon::now()->subHours(config('ticket.af_ticket_claimed_but_not_responded_email')))
            ->where('updated_at', '<', Carbon::now()->subHours(config('ticket.af_ticket_claimed_but_not_responded_email') - 1))
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

    public function sendEmail($ticketsClaimedButNotResponded, $admins)
    {
        foreach($ticketsClaimedButNotResponded as $ticket) {
            foreach($admins as $admin) {
              Mail::to($admin->adminProfile->email)->queue(new AfTicketClaimedButNotRespondedEmail($admin, $ticket->subject, $ticket->id));
            }
          }
    }
}
