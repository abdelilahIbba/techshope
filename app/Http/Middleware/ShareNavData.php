<?php

namespace App\Http\Middleware;

use App\Models\License;
use App\Models\Product;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Run the nav-shared DB queries exactly ONCE per request and share the results
 * with all views via View::share().
 *
 * Previously this was a View::composer('*', ...) callback which re-ran 4 DB
 * queries for every single view that was rendered (layouts, partials, components
 * — each triggers the wildcard composer). On a page with 6 sub-views that meant
 * 24 redundant DB round-trips per request.
 */
class ShareNavData
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $currency  = Setting::get('currency', 'DHs') ?: 'DHs';
            $cashLabel = Setting::get('cash_label', 'DHs') ?: 'DHs';

            View::share('notificationCount', Product::where('stock_quantity', '<=', 10)->count());
            View::share('navLicense',        License::current());
            View::share('currencySymbol',    $currency  === 'DZD' ? 'DHs' : $currency);
            View::share('cashPaymentLabel',  $cashLabel === 'DZD' ? 'DHs' : $cashLabel);
        } catch (\Exception $e) {
            // DB unavailable — provide safe defaults so views still render.
            View::share('notificationCount', 0);
            View::share('navLicense',        null);
            View::share('currencySymbol',    'DHs');
            View::share('cashPaymentLabel',  'DHs');
        }

        return $next($request);
    }
}
