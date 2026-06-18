<?php

namespace App\Console;

use App\Jobs\UpdateWialonPhones;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $controller = new \App\Http\Controllers\SimCardController();
            $controller->updateWialonPhones(new \Illuminate\Http\Request());
        })->dailyAt('08:00');
        $schedule->call(function () {
            $controller = new \App\Http\Controllers\SimCardController();
            $controller->updateSimCardFromWialon();
        })->dailyAt('08:10');

        // Procesa los jobs de la cola automáticamente cada minuto.
        // --stop-when-empty hace que el proceso termine cuando no hay más
        // trabajos pendientes, así el cron del servidor lo levanta y lo baja solo.
        $schedule->command('queue:work --stop-when-empty --tries=3')
            ->everyMinute()
            ->withoutOverlapping();
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
