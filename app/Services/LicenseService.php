<?php

namespace App\Services;

use App\Models\License;
use Illuminate\Support\Carbon;

class LicenseService
{
    // Periods available for generation
    public const PERIODS = [
        '1_week'   => '1 Semaine',
        '2_months' => '2 Mois',
        '3_months' => '3 Mois',
        '6_months' => '6 Mois',
        '1_year'   => '1 An',
        'lifetime' => 'À Vie (Lifetime)',
    ];

    // ------------------------------------------------------------------
    // Key generation (Admin side)
    // ------------------------------------------------------------------

    /**
     * Generate a signed offline license key for a given period.
     *
     * Key format:  DRG-{base64url(payload)}.{hmac32}
     * The payload is JSON: { expires_at, issued_at, period }
     */
    public function generate(string $period): string
    {
        if (! array_key_exists($period, self::PERIODS)) {
            throw new \InvalidArgumentException("Période invalide: {$period}");
        }

        $expiresAt = $this->expiryForPeriod($period);

        // Use hex encoding — fully case-insensitive, safe to strtoupper().
        // base64 is case-sensitive so it cannot survive the strtoupper() round-trip.
        $payload = bin2hex(json_encode([
            'expires_at' => $expiresAt->timestamp,
            'issued_at'  => now()->timestamp,
            'period'     => $period,
        ]));

        $signature = $this->sign($payload);

        // Upper-case for readability; hex + hmac hex are both safe.
        $raw = strtoupper('DRG-' . $payload . '.' . $signature);

        return $raw;
    }

    // ------------------------------------------------------------------
    // Validation (client side)
    // ------------------------------------------------------------------

    /**
     * Validate a license key.
     *
     * @return array{valid: bool, error?: string, expires_at?: Carbon, period?: string}
     */
    public function validate(string $key): array
    {
        $key = trim($key);

        if (! str_starts_with(strtoupper($key), 'DRG-')) {
            return ['valid' => false, 'error' => 'Format de clé invalide (doit commencer par DRG-).'];
        }

        $body = substr($key, 4);
        $dotPos = strrpos($body, '.');

        if ($dotPos === false) {
            return ['valid' => false, 'error' => 'Format de clé invalide (signature manquante).'];
        }

        // Normalise to lowercase — hex is case-insensitive, hmac hex is too.
        $payload   = strtolower(substr($body, 0, $dotPos));
        $givenSig  = strtolower(substr($body, $dotPos + 1));
        $expected  = $this->sign($payload);

        if (! hash_equals($expected, $givenSig)) {
            return ['valid' => false, 'error' => 'Signature invalide — clé corrompue ou falsifiée.'];
        }

        $data = json_decode(hex2bin($payload), true);

        if (! $data || ! isset($data['expires_at'], $data['issued_at'], $data['period'])) {
            return ['valid' => false, 'error' => 'Données de la clé corrompues.'];
        }

        $expiresAt = Carbon::createFromTimestamp($data['expires_at']);

        if ($expiresAt->isPast()) {
            return [
                'valid'      => false,
                'error'      => 'Licence expirée depuis le ' . $expiresAt->translatedFormat('d M Y à H:i') . '.',
                'expires_at' => $expiresAt,
            ];
        }

        return [
            'valid'      => true,
            'expires_at' => $expiresAt,
            'period'     => $data['period'],
        ];
    }

    // ------------------------------------------------------------------
    // Activation
    // ------------------------------------------------------------------

    /**
     * Validate and store the license key.  Returns the validation result array.
     */
    public function activate(string $key): array
    {
        $result = $this->validate($key);

        if (! $result['valid']) {
            return $result;
        }

        // Use DB::table to bypass Eloquent's mass-assignment protection and
        // guarantee the license is always stored with id = 1 (single-license system).
        \DB::table('licenses')->updateOrInsert(
            ['id' => 1],
            [
                'key'          => trim($key),
                'period'       => $result['period'],
                'activated_at' => now(),
                'last_seen_at' => now(),
                'expires_at'   => $result['expires_at'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        );

        License::flushCache();

        return $result;
    }

    // ------------------------------------------------------------------
    // Status helpers
    // ------------------------------------------------------------------

    public function isActive(): bool
    {
        return License::isActive();
    }

    public function getCurrent(): ?License
    {
        return License::current();
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function sign(string $payload): string
    {
        // Use the last 44 characters of the APP_KEY (the base64 encoded key body)
        $secret = config('app.key');

        return substr(hash_hmac('sha256', $payload, $secret), 0, 32);
    }

    private function expiryForPeriod(string $period): Carbon
    {
        return match ($period) {
            '1_week'   => now()->addWeek(),
            '2_months' => now()->addMonths(2),
            '3_months' => now()->addMonths(3),
            '6_months' => now()->addMonths(6),
            '1_year'   => now()->addYear(),
            'lifetime' => now()->addYears(100),
            default    => throw new \InvalidArgumentException("Période invalide: {$period}"),
        };
    }
}
