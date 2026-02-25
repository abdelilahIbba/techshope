<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = [
        'key',
        'period',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
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

        return $license !== null
            && $license->expires_at !== null
            && $license->expires_at->isFuture();
    }
}
