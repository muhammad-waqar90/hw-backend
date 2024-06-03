<?php

namespace App\Repositories\AF;

use App\DataObject\PermissionData;
use App\DataObject\Tickets\TicketCategoryData;
use App\DataObject\Tickets\TicketMessageTypeData;
use App\DataObject\Tickets\TicketStatusData;
use App\Models\Ticket;
use App\Models\TicketMessage;

class AfTicketRepository
{
    private Ticket $ticket;

    private TicketMessage $ticketMessage;

    public function __construct(Ticket $ticket, TicketMessage $ticketMessage)
    {
        $this->ticket = $ticket;
        $this->ticketMessage = $ticketMessage;
    }

    public function getTicketQuery($searchCategories, $searchStatus, $searchSubject = null)
    {
        return $this->ticket->select('tickets.*', 'us.name as username', 'ts.name as status', 'tc.name as categoryName')
            ->whereIn('ticket_category_id', $searchCategories)
            ->whereIn('ticket_status_id', $searchStatus)
            ->when($searchSubject, function ($query) use ($searchSubject) {
                $query->where('subject', 'LIKE', "%$searchSubject%");
            })
            ->leftJoin('users as us', 'us.id', '=', 'tickets.user_id')
            ->leftJoin('ticket_categories as tc', 'tc.id', '=', 'tickets.ticket_category_id')
            ->leftJoin('ticket_statuses as ts', 'ts.id', '=', 'tickets.ticket_status_id');
    }

    public function parseSearchCategories($category, $userPermissions)
    {
        $possibleCategories = $this->userTicketCategoriesFromPermissions($userPermissions);

        if (! $category) {
            unset($possibleCategories[TicketCategoryData::LESSON_QA - 1]);

            return $possibleCategories;
        }

        if (in_array($category, $possibleCategories)) {
            return [$category];
        }

        return false;
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

    public function userTicketCategoriesFromPermissions($userPermissions)
    {
        $possibleCategories = [];
        if (in_array(PermissionData::TICKET_SYSTEM_MANAGEMENT, $userPermissions)) {
            $possibleCategories[] = TicketCategoryData::SYSTEM;
        }
        if (in_array(PermissionData::TICKET_CONTENT_MANAGEMENT, $userPermissions)) {
            $possibleCategories[] = TicketCategoryData::CONTENT;
        }
        if (in_array(PermissionData::TICKET_REFUND_MANAGEMENT, $userPermissions)) {
            $possibleCategories[] = TicketCategoryData::REFUND;
        }
        if (in_array(PermissionData::TICKET_GDPR_MANAGEMENT, $userPermissions)) {
            $possibleCategories[] = TicketCategoryData::GDPR;
        }
        if (in_array(PermissionData::TICKET_LESSON_QA_MANAGEMENT, $userPermissions)) {
            $possibleCategories[] = TicketCategoryData::LESSON_QA;
        }

        return $possibleCategories;
    }

    public function getTicket($id)
    {
        return $this->ticket->find($id);
    }

    public function getTicketMessagesList($id)
    {
        return $this->ticketMessage->select('ticket_messages.*', 'us.name as username')
            ->where('ticket_messages.ticket_id', $id)
            ->leftJoin('users as us', 'us.id', '=', 'ticket_messages.user_id')
            ->latest('updated_at')
            ->simplePaginate(15);
    }

    public function getTicketMessageByType($id, $type)
    {
        return $this->ticketMessage
            ->where('type', $type)
            ->where('ticket_id', $id)
            ->latest()
            ->first();
    }

    public function getMyTicketList($userId, $searchStatus, $searchText = '')
    {
        $searchCategories = array_values(TicketCategoryData::getConstants());

        return $this->getTicketQuery($searchCategories, $searchStatus, $searchText)
            ->where('admin_id', $userId)
            ->where('ticket_category_id', '!=', TicketCategoryData::LESSON_QA)
            ->with('latestTicketMessage')
            ->latest('updated_at')
            ->simplePaginate(15);
    }

    public function unclaimAllTicketsFromAdmin($id, $adminName)
    {
        $activeTickets = $this->ticket->where('admin_id', $id)
            ->where('ticket_status_id', TicketStatusData::IN_PROGRESS)
            ->get();
        if ($activeTickets->isEmpty()) {
            return;
        }

        foreach ($activeTickets as $ticket) {
            $this->unclaimTicket($ticket, $id, $adminName);
        }
    }

    public function createMessage($userId, $ticketId, $message, $type)
    {
        return $this->ticketMessage->create([
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'message' => $message,
            'type' => $type,
        ]);
    }

    private function unclaimTicket(Ticket $ticket, $adminId, $adminName)
    {
        $ticket->admin_id = null;
        $ticket->ticket_status_id = TicketStatusData::UNCLAIMED;
        $ticket->seen_by_user = false;
        $ticket->save();

        $this->createMessage(
            $adminId,
            $ticket->id,
            'Admin "'.$adminName.'" has unclaimed your ticket',
            TicketMessageTypeData::SYSTEM_MESSAGE
        );
    }
}
