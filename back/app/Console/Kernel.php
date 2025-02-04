<?php

namespace App\Console;

use App\Http\Controllers\SimCardController;
use App\Jobs\UpdateWialonPhones;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $controller = new SimCardController();
            $controller->updateWialonPhones(request());
        })->dailyAt('02:43');// Se ejecuta todos los días a las 2:00 AM
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
