<?php

namespace App\Providers;

use App\Listeners\CertificateEventSubscriber;
use App\Listeners\CourseModuleEventSubscriber;
use App\Listeners\TicketAccountEventSubscriber;
use App\Listeners\TicketNotificationEventSubscriber;
use App\Listeners\UserEventSubscriber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        TicketNotificationEventSubscriber::class,
        CertificateEventSubscriber::class,
        TicketAccountEventSubscriber::class,
        CourseModuleEventSubscriber::class,
        UserEventSubscriber::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
