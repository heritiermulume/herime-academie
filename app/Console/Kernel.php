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
        // Intentionnellement vide: aucune dépendance au cron. L'annulation est gérée via jobs différés.
        
        // Nettoyage des tokens d'accès YouTube expirés (toutes les heures)
        $schedule->call(function() {
            \App\Models\VideoAccessToken::cleanupExpired();
        })->hourly()->name('youtube-cleanup-tokens');
        
        // Surveillance des activités suspectes (toutes les 6 heures)
        $schedule->call(function() {
            $securityService = app(\App\Services\VideoSecurityService::class);
            $securityService->monitorSuspiciousActivity();
        })->everySixHours()->name('youtube-monitor-activity');

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


