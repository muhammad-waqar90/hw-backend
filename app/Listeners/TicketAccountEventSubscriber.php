<?php

namespace App\Listeners;

use App\Events\Tickets\IuAccountRestored;
use App\Events\Tickets\IuAccountTrashed;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketAccountEventSubscriber implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handleIuAccountTrashed($event)
    {
        Ticket::where('user_id', $event->userId)->delete();
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handleIuAccountRestored($event)
    {
        Ticket::where('user_id', $event->userId)->restore();
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
            IuAccountTrashed::class,
            [TicketAccountEventSubscriber::class, 'handleIuAccountTrashed']
        );

        $events->listen(
            IuAccountRestored::class,
            [TicketAccountEventSubscriber::class, 'handleIuAccountRestored']
        );
    }
}
