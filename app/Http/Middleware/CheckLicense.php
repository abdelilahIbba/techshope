<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admins are always allowed — they own the license system.
        if ($user && $user->role === 'Admin') {
            return $next($request);
        }

        // Check if an active license exists.
        if (License::isActive()) {
            return $next($request);
        }

        // No valid license — redirect or respond.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Aucune licence active. Veuillez activer une licence pour utiliser le système.',
            ], 403);
        }

        return redirect()->route('license.activate')
            ->with('error', 'Aucune licence active. Veuillez activer une licence pour continuer.');
    }
}
