<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Expire QRIS transactions that have passed their expiry time
        // Run every minute to ensure timely expiration
        $schedule->command('qris:expire')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Optional: Clean up old notifications (older than 30 days)
        // $schedule->command('notifications:cleanup')
        //     ->daily()
        //     ->at('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
