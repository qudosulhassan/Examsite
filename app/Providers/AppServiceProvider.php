<?php

namespace App\Providers;

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
        // Share global settings across views safely
        try {
            \Illuminate\Support\Facades\View::composer('*', function ($view) {
                $view->with('globalSettings', \App\Models\Setting::allAsAssoc());
            });
        } catch (\Throwable $e) {
            // Ignore during migrations or initial bootstrap
        }
    }
}
