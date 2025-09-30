<?php

namespace App\Providers;

use App\Models\SIMCARD;
use App\Observers\SIMCARDObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        SIMCARD::observe(SIMCARDObserver::class);

    }
}
