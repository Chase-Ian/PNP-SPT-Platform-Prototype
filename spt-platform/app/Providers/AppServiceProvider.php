<?php

namespace App\Providers;

//use Illuminate\Support\Facades\Vite; local development
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
    //public function boot(): void
    //{
    //    Vite::prefetch(concurrency: 3);
    //}

    public function boot(): void
    {
        if (config('app.env') !== 'local' || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }

}
