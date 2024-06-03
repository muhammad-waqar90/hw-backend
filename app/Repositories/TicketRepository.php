<?php

namespace App\Repositories;

use App\DataObject\Tickets\TicketCategoryData;
use App\DataObject\Tickets\TicketMessageTypeData;
use App\DataObject\Tickets\TicketStatusData;
use App\Mail\IU\Ticket\IuTicketClosedEmail;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use App\Models\TicketSubject;
use App\Traits\FileSystemsCloudTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TicketRepository
{
    use FileSystemsCloudTrait;

    private TicketSubject $ticketSubject;

    private Ticket $ticket;

    private TicketCategory $ticketCategory;

    private TicketMessage $ticketMessage;

    public function __construct(TicketSubject $ticketSubject, Ticket $ticket, TicketCategory $ticketCategory, TicketMessage $ticketMessage)
    {
        $this->ticketSubject = $ticketSubject;
        $this->ticket = $ticket;
        $this->ticketCategory = $ticketCategory;
        $this->ticketMessage = $ticketMessage;
    }

    public function getTicketCategories()
    {
        return $this->ticketCategory->get();
    }

    public function createTicketSubject($categoryId, $name, $desc, $only_logged_in)
    {
        return $this->ticketSubject->create([
            'ticket_category_id' => $categoryId,
            'name' => $name,
            'desc' => $desc,
            'only_logged_in_users' => $only_logged_in,
        ]);
    }

    public function getTicketSubjectPaginatedList($searchText, $guestsOnly = false)
    {
        return $this->ticketSubject->select('id', 'name')
            ->when($searchText, function ($query, $searchText) {
                return $query->where('name', 'LIKE', "%$searchText%");
            })
            ->when($guestsOnly, function ($query) {
                return $query->where('only_logged_in_users', 0);
            })
            ->latest('id')
            ->paginate(20)
            ->appends(['searchText' => $searchText]);
    }

    public function getFullTicketSubjectList($guestsOnly = false)
    {
        return $this->ticketSubject->select('id', 'name')
            ->when($guestsOnly, function ($query) {
                return $query->where('only_logged_in_users', 0);
            })
            ->oldest('name')
            ->get();
    }

    public function getTicketSubject($id, $guestsOnly = false)
    {
        return $this->ticketSubject
            ->where('id', $id)
            ->when($guestsOnly, function ($query) {
                return $query->where('only_logged_in_users', 0);
            })
            ->with('ticketCategory')
            ->first();
    }

    public function updateTicketSubject($id, $categoryId, $name, $desc, $only_logged_in)
    {
        return $this->ticketSubject
            ->where('id', $id)
            ->update([
                'ticket_category_id' => $categoryId,
                'name' => $name,
                'desc' => $desc,
                'only_logged_in_users' => $only_logged_in,
            ]);
    }

    /**
     * @param  TicketSubject  $ticketSubject
     */
    public function deleteTicketSubject($ticketSubject)
    {
        return $ticketSubject->delete();
    }

    /**
     * @return mixed
     */
    public function createGuestTicket($categoryId, $email, $subject, $log)
    {
        return $this->ticket->create([
            'ticket_category_id' => $categoryId,
            'ticket_status_id' => TicketStatusData::UNCLAIMED,
            'user_email' => $email,
            'subject' => $subject,
            'log' => json_encode($log),
        ]);
    }

    /**
     * @return mixed
     */
    public function createIuTicket($categoryId, $userId, $subject, $log)
    {
        return $this->ticket->create([
            'ticket_category_id' => $categoryId,
            'ticket_status_id' => TicketStatusData::UNCLAIMED,
            'user_id' => $userId,
            'subject' => $subject,
            'seen_by_user' => true,
            'log' => json_encode($log),
        ]);
    }

    /**
     * @return mixed
     */
    public function createMessage($userId, $ticketId, $message, $type)
    {
        return $this->ticketMessage->create([
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'message' => $message,
            'type' => $type,
        ]);
    }

    public function isTicketMessageExist($userId, $question, $lessonId)
    {
        return $this->ticketMessage
            ->join('lesson_ticket as lt', 'lt.ticket_id', 'ticket_messages.ticket_id')
            ->where('lt.lesson_id', $lessonId)
            ->where('ticket_messages.user_id', $userId)
            ->where('ticket_messages.type', TicketMessageTypeData::USER_MESSAGE)
            ->where('ticket_messages.message', $question)
            ->exists();
    }

    public function getTicketMessagesList($id)
    {
        return $this->ticketMessage->select('ticket_messages.*', 'us.name as username')
            ->where('ticket_messages.type', '!=', TicketMessageTypeData::ADMIN_ONLY_SYSTEM_MESSAGE)
            ->where('ticket_messages.ticket_id', $id)
            ->leftJoin('users as us', 'us.id', '=', 'ticket_messages.user_id')
            ->latest('updated_at')
            ->simplePaginate(20);
    }

    public function getTicket($id)
    {
        return $this->ticket->find($id);
    }

    public function getTicketQuery($searchCategories, $searchStatus, $searchSubject = null)
    {
        return $this->ticket->select('tickets.*', 'af.name as adminName', 'ts.name as status')
            ->whereIn('ticket_category_id', $searchCategories)
            ->whereIn('ticket_status_id', $searchStatus)
            ->when($searchSubject, function ($query) use ($searchSubject) {
                $query->where('subject', 'LIKE', "%$searchSubject%");
            })
            ->leftJoin('users as af', 'af.id', '=', 'tickets.admin_id')
            ->leftJoin('ticket_statuses as ts', 'ts.id', '=', 'tickets.ticket_status_id');
    }

    public function getTicketDetails($id)
    {
        $searchCategories = array_values(TicketCategoryData::getConstants());
        $searchStatus = array_values(TicketStatusData::getConstants());

        return $this->getTicketQuery($searchCategories, $searchStatus)
            ->where('ticket_category_id', '!=', TicketCategoryData::LESSON_QA)
            ->where('tickets.id', $id)
            ->first();
    }

    public function getMyTicketList($userId, $searchStatus, $searchText = '')
    {
        $searchCategories = array_values(TicketCategoryData::getConstants());

        return $this->getTicketQuery($searchCategories, $searchStatus, $searchText)
            ->where('user_id', $userId)
            ->where('ticket_category_id', '!=', TicketCategoryData::LESSON_QA)
            ->with('latestTicketMessage', function ($query) {
                $query->where('type', '!=', TicketMessageTypeData::ADMIN_ONLY_SYSTEM_MESSAGE);
            })
            ->latest('updated_at')
            ->simplePaginate(20);
    }

    public function parseSearchStatus($status)
    {
        $allStatuses = array_values(TicketStatusData::getConstants());

        if (! $status) {
            return $allStatuses;
        }
        if (! in_array($status, $allStatuses)) {
            return false;
        }
        if ($status == TicketStatusData::UNCLAIMED) {
            return [TicketStatusData::UNCLAIMED, TicketStatusData::REOPENED];
        }

        return [(int) $status];
    }

    public function ticketSeenByUser($id, $value)
    {
        return DB::table('tickets')->where('id', $id)
            ->update([
                'seen_by_user' => $value,
            ]);
    }

    public function ticketSeenByAdmin($id, $value)
    {
        return DB::table('tickets')->where('id', $id)
            ->update([
                'seen_by_admin' => $value,
            ]);
    }

    public function onIuTicketAutoResolve(Ticket $ticket)
    {
        $ticket
            ->where('id', $ticket->id)
            ->update([
                'ticket_status_id' => TicketStatusData::RESOLVED,
                'seen_by_user' => 1,
            ]);

        $this->createMessage(
            $ticket->admin_id,
            $ticket->id,
            'Ticket auto closed as user not responded in 72 hours after admin response',
            TicketMessageTypeData::SYSTEM_MESSAGE
        );

        Mail::to($ticket->user->userProfile->email)->queue(new IuTicketClosedEmail($ticket->user, $ticket->subject, $ticket->id));

        return true;
    }

    public function markAsResolved($ticketId)
    {
        return $this->ticket->where('id', $ticketId)
            ->update([
                'ticket_status_id' => TicketStatusData::RESOLVED,
            ]);
    }

    public function getLessonQaTicketsQuery($userId, $lessonId, $statuses)
    {
        return $this->ticket
            ->where('user_id', $userId)
            ->whereIn('ticket_status_id', $statuses)
            ->where('ticket_category_id', TicketCategoryData::LESSON_QA)
            ->whereHas('lesson', function ($query) use ($lessonId) {
                $query->where('lesson_id', $lessonId);
            })
            ->with('ticketMessages')
            ->latest('updated_at');
    }

    public static function getThumbnailS3StoragePath($ticketid)
    {
        return 'tickets/'.$ticketid.'/';
    }

    public function handleTicketAssets($userId, $assets, $ticketId, $type)
    {
        foreach ($assets as $asset) {
            $thumbnail = $this->uploadFile($this->getThumbnailS3StoragePath($ticketId), $asset);
            $message[] = $this->createMessage(
                $userId,
                $ticketId,
                $thumbnail,
                $type
            );
        }

        return $message;
    }

    public function checkIfAnyUnResolvedTicketsExist($userId, $categories)
    {
        return $this->ticket
            ->where('user_id', $userId)
            ->whereIn('ticket_status_id', [TicketStatusData::UNCLAIMED, TicketStatusData::IN_PROGRESS, TicketStatusData::REOPENED])
            ->whereIn('ticket_category_id', $categories)
            ->exists();
    }
}
