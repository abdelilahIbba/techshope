<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    /**
     * Request-level in-memory cache, keyed by setting key.
     * Loaded once on the first get() call; invalidated on every set().
     * Eliminates the N×SELECT pattern when many settings are read per request.
     *
     * @var array<string, self>|null
     */
    private static ?array $cache = null;

    public static function get($key, $default = null)
    {
        if (self::$cache === null) {
            // Load all rows in one query and index by key.
            self::$cache = self::all()->keyBy('key')->all();
        }

        $setting = self::$cache[$key] ?? null;

        if (! $setting) {
            return $default;
        }

        return match($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => (bool) $setting->value,
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    public static function set($key, $value, $type = 'string')
    {
        if ($type === 'json') {
            $value = json_encode($value);
        }

        // Invalidate cache so the next get() sees the freshly written value.
        self::$cache = null;

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}
