<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Observers\AcademicYearObserver;
use Illuminate\Support\Facades\Storage;
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
        // Pastikan folder tmp/imports selalu ada
        if (! Storage::disk('local')->exists('tmp/imports')) {
            Storage::disk('local')->makeDirectory('tmp/imports');
        }
    }
}
