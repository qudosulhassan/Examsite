<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Cache tag or key for settings.
     */
    const CACHE_KEY = 'site_settings_all';

    /**
     * Get setting value by key (with caching).
     */
    public static function get(string $key, $default = null)
    {
        $all = self::allAsAssoc();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /**
     * Set setting value by key and invalidate cache.
     */
    public static function set(string $key, $value): self
    {
        $record = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        \Illuminate\Support\Facades\Cache::forget(self::CACHE_KEY);

        return $record;
    }

    /**
     * Get all settings as an associative key => value array.
     */
    public static function allAsAssoc(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(self::CACHE_KEY, 3600, function () {
            try {
                return self::all()->pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Clear the cached settings.
     */
    public static function clearCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget(self::CACHE_KEY);
    }
}
