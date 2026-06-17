<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process abandoned carts every hour
        $schedule->command('carts:process-abandoned --idle=120 --batch=50')
            ->hourly()
            ->withoutOverlapping();

        // Notify about expiring loyalty points daily
        $schedule->command('loyalty:notify-expiry --days=30 --batch=100')
            ->dailyAt('09:00')
            ->withoutOverlapping();

        // Auto-cancel return/exchange requests past the 3-day delivery window
        $schedule->command('orders:expire-requests --force')
            ->dailyAt('00:00')
            ->withoutOverlapping();

        // Remind customers 1 day before return window expires (they ordered 2 days ago)
        $schedule->command('orders:remind-window-expiry')
            ->dailyAt('10:00')
            ->withoutOverlapping();
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
