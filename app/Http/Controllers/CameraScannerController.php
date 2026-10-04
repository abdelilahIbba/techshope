<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Product;

/**
 * CameraScannerController
 *
 * Handles the camera-based barcode scanner feature.
 * This controller manages:
 *   1. The mobile scanner page (accessible via LAN link).
 *   2. The SSE (Server-Sent Events) stream that pushes scanned barcodes
 *      from mobile → desktop POS in real time.
 *   3. The endpoint the mobile page POSTs scanned barcodes to.
 *
 * Architecture (no WebSocket dependency):
 *   Mobile Camera  →  POST /pos/scanner/push  →  Cache (keyed by session token)
 *                                                        ↓
 *   Desktop POS    ←  GET  /pos/scanner/stream ←  SSE polling Cache key
 *
 * The session token is a short-lived UUID stored in the browser session so
 * only the correct desktop tab receives the barcode.
 */
class CameraScannerController extends Controller
{
    /** How long (seconds) a scan result stays in Cache before expiry */
    private const CACHE_TTL = 120;

    /** How many seconds the SSE stream stays open before the client reconnects */
    private const SSE_MAX_DURATION = 25;

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Token Management
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate (or return existing) a scanner session token for the
     * current authenticated desktop user.
     *
     * GET /pos/scanner/token   → { token: "xxxx", mobile_url: "http://..." }
     */
    public function getToken(Request $request): JsonResponse
    {
        $token = $request->session()->get('scanner_token');

        if (! $token) {
            $token = Str::random(32);
            $request->session()->put('scanner_token', $token);
        }

        // Build the mobile URL — use the current request host so it works on LAN
        $mobileUrl = url('/pos/scanner/mobile/' . $token);

        return response()->json([
            'token'      => $token,
            'mobile_url' => $mobileUrl,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Mobile Scanner Page
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Render the mobile-optimised scanner page.
     * This page is publicly accessible (no auth required) so the phone
     * user doesn't need to log in — the token acts as the shared secret.
     *
     * GET /pos/scanner/mobile/{token}
     */
    public function mobilePage(string $token)
    {
        // Basic token validation: 32 alphanumeric chars
        if (! preg_match('/^[A-Za-z0-9]{32}$/', $token)) {
            abort(404);
        }

        return view('pos.scanner-mobile', compact('token'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Push (Mobile → Server)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Accept a scanned barcode from the mobile page and store it in Cache
     * so the desktop SSE stream can pick it up.
     *
     * POST /pos/scanner/push
     * Body: { token: string, barcode: string }
     */
    public function push(Request $request): JsonResponse
    {
        $request->validate([
            'token'   => ['required', 'string', 'size:32', 'regex:/^[A-Za-z0-9]+$/'],
            'barcode' => ['required', 'string', 'max:255'],
        ]);

        $token   = $request->input('token');
        $barcode = trim($request->input('barcode'));

        if (empty($barcode)) {
            return response()->json(['status' => 'error', 'message' => 'Barcode vide'], 422);
        }

        $cacheKey = 'scanner:' . $token;

        Cache::put($cacheKey, [
            'barcode'    => $barcode,
            'scanned_at' => now()->toISOString(),
        ], self::CACHE_TTL);

        return response()->json(['status' => 'ok']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. SSE Stream (Server → Desktop)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Open a Server-Sent Events stream.
     * The desktop POS page listens here; when a barcode arrives in Cache,
     * it is forwarded as an SSE event and the cache entry is deleted.
     *
     * GET /pos/scanner/stream
     */
    public function stream(Request $request)
    {
        $token = $request->session()->get('scanner_token');

        if (! $token) {
            // Return an SSE-formatted error so the client JS can handle it
            return response("event: error\ndata: {\"message\":\"No token\"}\n\n", 200, [
                'Content-Type'  => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $cacheKey  = 'scanner:' . $token;
        $startTime = time();

        return response()->stream(function () use ($cacheKey, $startTime) {
            // Send an initial heartbeat so the browser knows the stream is alive
            echo "event: connected\ndata: {\"status\":\"listening\"}\n\n";
            if (ob_get_level()) ob_flush();
            flush();

            while ((time() - $startTime) < self::SSE_MAX_DURATION) {
                $payload = Cache::get($cacheKey);

                if ($payload) {
                    Cache::forget($cacheKey);

                    $json = json_encode($payload);
                    echo "event: barcode\ndata: {$json}\n\n";
                    if (ob_get_level()) ob_flush();
                    flush();

                    // After sending one barcode, close so the client reconnects
                    // (prevents stale SSE connections from holding FPM workers)
                    return;
                }

                // Heartbeat every 3 s to keep connection alive through proxies
                echo ": heartbeat\n\n";
                if (ob_get_level()) ob_flush();
                flush();

                sleep(3);
            }

            // Stream timeout — tell the client to reconnect
            echo "event: timeout\ndata: {}\n\n";
            if (ob_get_level()) ob_flush();
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. Barcode Lookup (Desktop AJAX)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Look up a product by barcode and return its data as JSON.
     * Used by the desktop POS JS to verify a scanned barcode before
     * injecting it into the existing scanAndAdd() flow.
     *
     * GET /pos/scanner/lookup?barcode=xxxx
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
        ]);

        $barcode = $request->input('barcode');

        $product = Product::with(['variants', 'category'])
            ->where('barcode', $barcode)
            ->first();

        if (! $product) {
            return response()->json([
                'found'   => false,
                'message' => 'Produit non trouvé',
            ], 404);
        }

        return response()->json([
            'found'   => true,
            'barcode' => $barcode,
            'product' => [
                'id'       => $product->id,
                'name'     => $product->name,
                'price'    => $product->price,
                'category' => optional($product->category)->name,
            ],
        ]);
    }
}
