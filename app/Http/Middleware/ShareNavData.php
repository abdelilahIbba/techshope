<?php

namespace App\Http\Middleware;

use App\Models\License;
use App\Models\ProductVariant;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        // Skip expensive nav payload for non-HTML or mutation requests.
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName === '' || ! str_starts_with($routeName, 'pos.')) {
            return $next($request);
        }

        try {
            $currency  = Cache::remember('nav:currency', now()->addMinutes(5), fn () => Setting::get('currency', 'DHs') ?: 'DHs');
            $cashLabel = Cache::remember('nav:cash_label', now()->addMinutes(5), fn () => Setting::get('cash_label', 'DHs') ?: 'DHs');
            $notificationCount = Cache::remember('nav:low_stock_count', now()->addSeconds(30), fn () => ProductVariant::where('stock_quantity', '<=', 10)->count());

            View::share('notificationCount', $notificationCount);
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
