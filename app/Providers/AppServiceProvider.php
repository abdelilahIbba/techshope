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
     *
     * Nav data (notificationCount, navLicense, currencySymbol, cashPaymentLabel)
     * is now shared via App\Http\Middleware\ShareNavData which runs exactly once
     * per request. The previous View::composer('*', ...) was firing 4 DB queries
     * for every single view rendered (layouts, partials, components) — causing
     * 16–40 redundant DB round-trips per page.
     */
    public function boot(): void
    {
        //
    }
}

