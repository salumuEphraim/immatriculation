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
        // Vérification quotidienne des expirations de documents à 8h00
        $schedule->command('documents:check-expirations')
                 ->dailyAt('08:00')
                 ->description('Vérifie les documents expirants et envoie les notifications');
        
        // Alternative: vérification toutes les heures pour plus de précision
        // $schedule->command('documents:check-expirations')->hourly();
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
