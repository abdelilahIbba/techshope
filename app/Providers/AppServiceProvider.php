<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\License;
use App\Models\Product;
use App\Models\Setting;

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
        // Share notification count and license across all views
        View::composer('*', function ($view) {
            try {
                $notificationCount = Product::where('stock_quantity', '<=', 10)->count();
                $view->with('notificationCount', $notificationCount);
                $view->with('navLicense', License::current());
                $currencySymbol = Setting::get('currency', 'DHs');
                if (! $currencySymbol || $currencySymbol === 'DZD') {
                    $currencySymbol = 'DHs';
                }
                $cashLabel = Setting::get('cash_label', 'DHs');
                if (! $cashLabel || $cashLabel === 'DZD') {
                    $cashLabel = 'DHs';
                }
                $view->with('currencySymbol', $currencySymbol);
                $view->with('cashPaymentLabel', $cashLabel);
            } catch (\Exception $e) {
                // DB unavailable — provide safe defaults so views still render
                $view->with('notificationCount', 0);
                $view->with('navLicense', null);
                $view->with('currencySymbol', 'DHs');
                $view->with('cashPaymentLabel', 'DHs');
            }
        });
    }
}
