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
     * Retrieve the single active license record (always id = 1).
     */
    public static function current(): ?self
    {
        return self::find(1);
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

        if (! $license->last_seen_at || $now->gt($license->last_seen_at)) {
            $license->forceFill(['last_seen_at' => $now])->saveQuietly();
        }

        return $license->expires_at->isFuture();
    }
}
