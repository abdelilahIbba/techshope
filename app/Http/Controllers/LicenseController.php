<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    public function __construct(private readonly LicenseService $licenseService) {}

    /**
     * Show the license activation page.
     */
    public function activate(): View
    {
        $current = $this->licenseService->getCurrent();

        return view('license.activate', [
            'current' => $current,
        ]);
    }

    /**
     * Process the license key submission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'license_key' => ['required', 'string', 'min:10'],
        ], [
            'license_key.required' => 'Veuillez saisir une clé de licence.',
            'license_key.min'      => 'La clé de licence est trop courte.',
        ]);

        $result = $this->licenseService->activate($request->input('license_key'));

        if (! $result['valid']) {
            return back()
                ->withInput()
                ->withErrors(['license_key' => $result['error']]);
        }

        return redirect()->route('pos.index')
            ->with('success', 'Licence activée avec succès ! Expire le ' . $result['expires_at']->translatedFormat('d M Y') . '.');
    }

    /**
     * Admin-only: generate a new license key (AJAX / form).
     */
    public function generate(Request $request)
    {
        // Only admins may generate licenses.
        if ($request->user()?->role !== 'Admin') {
            abort(403, 'Seul l\'administrateur peut générer des licences.');
        }

        $request->validate([
            'period' => ['required', 'string', 'in:' . implode(',', array_keys(LicenseService::PERIODS))],
        ]);

        $key = $this->licenseService->generate($request->input('period'));

        return response()->json(['key' => $key]);
    }
}
