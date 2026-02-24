<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Product;

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
        // Share notification count across all views
        View::composer('*', function ($view) {
            $notificationCount = Product::where('stock_quantity', '<=', 10)->count();
            $view->with('notificationCount', $notificationCount);
        });
    }
}
