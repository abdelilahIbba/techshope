<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermissionTo($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Accès refusé.',
                    'permission' => $permission,
                ], 403);
            }

            $redirectUrl = Route::has('pos.index')
                ? route('pos.index')
                : route('home');

            return response()->view('auth.permission-denied', [
                'message' => 'Accès refusé pour cette page/action.',
                'permission' => $permission,
                'redirectUrl' => $redirectUrl,
                'countdownSeconds' => 5,
            ], 403);
        }

        return $next($request);
    }
}
