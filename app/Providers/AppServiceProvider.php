<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Ambil dari config
        $globalData = config('customvariabel');

        // Bagikan ke semua view
        View::share('globalVar', $globalData);

        view()->composer('*', function ($view) {
            if(auth()->check()){
                $user = auth()->user();

                // ambil data notifikasi
                $latestNotification = $user->notifications()->latest()->limit(10)->get();

                // Bagikan ke semua view
                $view->with('latestNotification', $latestNotification);
            }
        });
    }
}
