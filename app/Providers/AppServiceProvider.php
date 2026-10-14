<?php

namespace App\Providers;

use App\Models\Menu;
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
        // Share low-stock menu count to admin dashboard & layouts.
        \View::composer('layouts.admin', function ($view) {
            $ambang = 5; // ambang rendah untuk notifikasi
            $count = Menu::where('stok', '<=', $ambang)
                ->orWhere('ketersediaan', false)
                ->count();
            $view->with('lowStockCount', $count);
        });
    }
}
