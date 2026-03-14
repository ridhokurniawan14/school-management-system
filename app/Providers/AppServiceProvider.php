<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Observers\AcademicYearObserver;
use Illuminate\Support\ServiceProvider;

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
        AcademicYear::observe(AcademicYearObserver::class);
    }
}
