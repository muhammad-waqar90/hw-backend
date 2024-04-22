<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Ticket\SendAfTicketReminderEmail::class,
        Commands\Ticket\SendIuTicketReminderEmail::class,
        Commands\Ticket\SendIuTicketClosedEmail::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         //$schedule->command('inspire')->hourly();
         //$schedule->command('send:testEmail')->cron('* * * * *');
        $schedule->command('clean:verification_codes')->hourly();
        $schedule->command('clean:age_verification_codes')->hourly();
        $schedule->command('clean:password_resets')->hourly();
        $schedule->command('clean:video_progress')->weeklyOn(1, '04:00');
        $schedule->command('clean:stuck_bulk_imports')->hourly();
        $schedule->command('clean:gdpr_exports')->hourly();
        $schedule->command('send:afTicketReminderEmail')->hourly();
        $schedule->command('send:afTicketOnHoldReminderEmail')->hourly();
        $schedule->command('send:afTicketClaimedButNotRespondedEmail')->hourly();
        $schedule->command('send:iuTicketReminderEmail')->hourly();
        $schedule->command('send:iuTicketClosedEmail')->hourly();
        $schedule->command('clean:restore_users')->hourly();
        $schedule->command('archive:global_notifications')->hourly();
        $schedule->command('deactivate:adverts')->hourly();
        $schedule->command('publish:lessons')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
