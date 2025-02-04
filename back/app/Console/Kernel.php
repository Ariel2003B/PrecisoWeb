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
