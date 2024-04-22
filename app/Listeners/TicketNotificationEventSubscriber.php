<?php

namespace App\Listeners;

use App\Listeners\AbstractSubscriber;
use App\DataObject\Notifications\NotificationTypeData;
use App\Events\Notifications\Tickets\AfTicketClaimed;
use App\Events\Notifications\Tickets\AfTicketReplied;
use App\Events\Notifications\Tickets\AfTicketResolved;
use App\Events\Notifications\Tickets\AfTicketUnclaimed;
use App\Events\Notifications\Tickets\IuTicketNotClaimed;
use App\Events\Notifications\Tickets\IuTicketReplied;
use App\Events\Notifications\Tickets\IuTicketResolved;
use App\Mail\IU\Ticket\IuTicketResponseEmail;
use App\Mail\IU\Ticket\IuTicketResolveEmail;
use App\Mail\IU\Ticket\IuTicketClaimedEmail;
use App\Mail\IU\Ticket\IuTicketUnclaimedEmail;
use App\Repositories\NotificationRepository;
use App\Repositories\IU\IuUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketNotificationEventSubscriber extends AbstractSubscriber
{
    /**
     * @var NotificationRepository
     */
    private $notificationRepository;

    /**
     * @var IuUserRepository
     */
    private $iuUserRepository;

    /**
     * TicketNotificationEventSubscriber constructor.
     * @param IuUserRepository $iuUserRepository
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(NotificationRepository $notificationRepository, IuUserRepository $iuUserRepository)
    {
        $this->notificationRepository = $notificationRepository;
        $this->iuUserRepository = $iuUserRepository;
    }

    /**
     * Handle IU Ticket Replied
     */
    public function handleIuTicketReplied($event) {
        $this->notificationRepository->createNotification(
            $event->userId,
            Lang::get('notifications.titles.ticket.reply'),
            (strlen($event->description) > 50) ? substr($event->description,0,50).'...' : $event->description,
            NotificationTypeData::SUPPORT_TICKET,
            $this->generateAction($event->ticketId)
        );
    }

    /**
     * Handle AF Ticket Replied
     */
    public function handleAfTicketReplied($event) {
        DB::beginTransaction();
        try {
            $title = Lang::get('notifications.titles.ticket.reply');
            $description = (strlen($event->description) > 50) ? substr($event->description,0,50).'...' : $event->description;
            $type = NotificationTypeData::SUPPORT_TICKET;
            $action = $this->generateAction($event->ticketId);
            $iuTicketLink = 'iu/tickets/'.$event->ticketId;

            // lesson Q&A ticket
            if($event->iuTicketLinkIds !== null) {
                $title = Lang::get('notifications.qa.title', ['lesson' => $event->subject]);
                $type = NotificationTypeData::LESSON_QA_TICKET;
                $action = $this->generateActionQA($event->iuTicketLinkIds['lessonId'], $event->iuTicketLinkIds['courseId']);
                $iuTicketLink = 'iu/courses/'.$event->iuTicketLinkIds['courseId'].'/lesson/'.$event->iuTicketLinkIds['lessonId'].'#qa';
            }

            $this->notificationRepository->createNotification(
                $event->userId,
                $title,
                $description,
                $type,
                $action
            );

            $iuUser = $this->iuUserRepository->getUser($event->userId, true);
            Mail::to($iuUser->userProfile->email)->queue(new IuTicketResponseEmail($iuUser, $event->description, $event->subject, $iuTicketLink));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: TicketNotificationEventSubscriber@handleAfTicketReplied', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }

    }

    /**
     * Handle IU Ticket Resolved
     */
    public function handleIuTicketResolved($event) {
        $this->notificationRepository->createNotification(
            $event->userId,
            Lang::get('notifications.titles.ticket.status'),
            Lang::get('notifications.titles.ticket.resolved', ['username' => $event->userName]),
            NotificationTypeData::SUPPORT_TICKET,
            $this->generateAction($event->ticketId)
        );
    }

    /**
     * Handle AF Ticket Resolved
     */
    public function handleAfTicketResolved($event) {
        DB::beginTransaction();
        try {
            $this->notificationRepository->createNotification(
                $event->userId,
                Lang::get('notifications.titles.ticket.status'),
                Lang::get('notifications.titles.ticket.resolved', ['username' => $event->userName]),
                NotificationTypeData::SUPPORT_TICKET,
                $this->generateAction($event->ticketId)
            );

            $iuUser = $this->iuUserRepository->getUser($event->userId, true);
            Mail::to($iuUser->userProfile->email)->queue(new IuTicketResolveEmail($iuUser, $event->subject, $event->userName, $event->message, $event->ticketId));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: TicketNotificationEventSubscriber@handleAfTicketResolved', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }

    }

    /**
     * Handle AF Ticket Claimed
     */
    public function handleAfTicketClaimed($event)
    {
        DB::beginTransaction();
        try {
            $this->notificationRepository->createNotification(
                $event->userId,
                Lang::get('notifications.titles.ticket.status'),
                Lang::get('notifications.titles.ticket.claimed', ['username' => $event->userName]),
                NotificationTypeData::SUPPORT_TICKET,
                $this->generateAction($event->ticketId)
            );

            $iuUser = $this->iuUserRepository->getUser($event->userId, true);
            Mail::to($iuUser->userProfile->email)->queue(new IuTicketClaimedEmail($iuUser, $event->subject, $event->userName, $event->ticketId));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: TicketNotificationEventSubscriber@handleAfTicketClaimed', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }

    }

    /**
     * Handle AF Ticket Unclaimed
     */
    public function handleAfTicketUnclaimed($event)
    {
        DB::beginTransaction();
        try {
            $this->notificationRepository->createNotification(
                $event->userId,
                Lang::get('notifications.titles.ticket.status'),
                Lang::get('notifications.titles.ticket.unclaimed', ['username' => $event->userName]),
                NotificationTypeData::SUPPORT_TICKET,
                $this->generateAction($event->ticketId)
            );

            $iuUser = $this->iuUserRepository->getUser($event->userId, true);
            Mail::to($iuUser->userProfile->email)->queue(new IuTicketUnclaimedEmail($iuUser, $event->subject, $event->userName, $event->ticketId));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: TicketNotificationEventSubscriber@handleAfTicketUnclaimed', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }

    }

    /**
     * Handle IU Ticket Not Claimed
     */
    public function handleIuTicketNotClaimed($event)
    {
        $this->notificationRepository->createNotification(
            $event->userId,
            Lang::get('notifications.titles.ticket.notClaimed', ['id' => $event->ticketId]),
            Lang::get('notifications.body.ticket.notClaimed', ['id' => $event->ticketId, 'subject' => $event->subject]),
            NotificationTypeData::SUPPORT_TICKET,
            $this->generateAction($event->ticketId)
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            IuTicketReplied::class,
            [TicketNotificationEventSubscriber::class, 'handleIuTicketReplied']
        );

        $events->listen(
            AfTicketReplied::class,
            [TicketNotificationEventSubscriber::class, 'handleAfTicketReplied']
        );

        $events->listen(
            IuTicketResolved::class,
            [TicketNotificationEventSubscriber::class, 'handleIuTicketResolved']
        );

        $events->listen(
            AfTicketResolved::class,
            [TicketNotificationEventSubscriber::class, 'handleAfTicketResolved']
        );

        $events->listen(
            AfTicketClaimed::class,
            [TicketNotificationEventSubscriber::class, 'handleAfTicketClaimed']
        );

        $events->listen(
            AfTicketUnclaimed::class,
            [TicketNotificationEventSubscriber::class, 'handleAfTicketUnclaimed']
        );

        $events->listen(
            IuTicketNotClaimed::class,
            [TicketNotificationEventSubscriber::class, 'handleIuTicketNotClaimed']
        );
    }

    /**
     * generate action for ticket notifications
     * @param $id - ticket id
     */
    private function generateAction($id)
    {
        return [
            "redirect" => [
                "id" => $id
            ]
        ];
    }

    /**
     * generate action for lesson Q&A ticket notifications
     * @param $id - lesson id
     */
    private function generateActionQA($lessonId, $courseId)
    {
        return [
            "redirect" => [
                "lessonId" => $lessonId,
                "courseId" => $courseId
            ]
        ];
    }
}
