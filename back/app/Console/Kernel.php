<?php

namespace App\Console;

use App\Http\Controllers\SimCardController;
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
            try {
                $controller = app(SimCardController::class);
                $controller->updateWialonPhones(new \Illuminate\Http\Request());
                Log::info("✅ Tarea programada ejecutada correctamente.");
            } catch (\Exception $e) {
                Log::error("❌ Error en tarea programada: " . $e->getMessage());
            }
        })->dailyAt('03:24');// Se ejecuta todos los días a las 2:00 AM
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
