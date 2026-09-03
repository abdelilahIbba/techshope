<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class License extends Model
{
    private const CLOCK_ROLLBACK_TOLERANCE_MINUTES = 5;

    protected $fillable = [
        'key',
        'period',
        'activated_at',
        'last_seen_at',
        'expires_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /**
     * Request-level cache — prevents repeated SELECT queries when current() is
     * called from CheckLicense middleware, ShareNavData middleware, and
     * settingsPayload() all within the same request.
     */
    private static bool $fetched = false;
    private static ?self $sharedInstance = null;

    /**
     * Reset the static in-process cache.
     * Called automatically on save (via boot) so tests and sequential
     * activations always see fresh data.
     */
    public static function flushCache(): void
    {
        self::$fetched = false;
        self::$sharedInstance = null;
    }

    /**
     * Register model event hooks.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Flush the in-process cache whenever a license record is written,
        // so that the next call to current() or isActive() re-reads from DB.
        static::saved(function () {
            static::flushCache();
        });

        static::deleted(function () {
            static::flushCache();
        });
    }


    /**
     * Retrieve the single active license record (always id = 1).
     * Both LicenseService::activate() and the test helper activeLicense()
     * guarantee the license is stored at id = 1 via DB::table::updateOrInsert.
     * Result is cached for the duration of the current PHP process (one request).
     */
    public static function current(): ?self
    {
        if (! self::$fetched) {
            self::$sharedInstance = self::find(1);
            self::$fetched = true;
        }
        return self::$sharedInstance;
    }

    /**
     * Return true if a license is stored and not yet expired.
     */
    public static function isActive(): bool
    {
        $license = self::current();

        if ($license === null || $license->expires_at === null) {
            return false;
        }

        $now = now();

        if ($license->last_seen_at instanceof Carbon
            && $now->lt($license->last_seen_at->copy()->subMinutes(self::CLOCK_ROLLBACK_TOLERANCE_MINUTES))) {
            return false;
        }

        // Only write last_seen_at once per minute — previously this ran a DB UPDATE
        // on every single request because $now->gt($last_seen_at) is always true.
        $shouldUpdate = ! $license->last_seen_at
            || $now->diffInSeconds($license->last_seen_at) >= 60;

        if ($shouldUpdate) {
            $license->forceFill(['last_seen_at' => $now])->saveQuietly();
        }

        return $license->expires_at->isFuture();
    }
}
