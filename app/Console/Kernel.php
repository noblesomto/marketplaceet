<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register custom commands.
     */
    protected $commands = [
        \App\Console\Commands\MarkExpiredBoosts::class,
        \App\Console\Commands\MigrateAdvertImagesToSpatie::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('boosts:expire')->everySixHours();
        $schedule->command('feed:google')->everySixHours();
        $schedule->command('sitemap:generate')->everySixHours();
        $schedule->command('cleanup:trusted-devices')->monthly();
        $schedule->command('cleanup:device-tokens')->monthly();
        $schedule->command('temp:cleanup-images --hours=24')->daily()->at('02:00');
        $schedule->command('activitylog:clean')->weekly();
        $schedule->command('buydirect:process')->everyFifteenMinutes()->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

