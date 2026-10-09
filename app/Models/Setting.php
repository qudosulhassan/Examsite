<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value site settings.
 *
 * The core site (id 1) keeps using the global `settings` table. Every other site reads and
 * writes its own rows in `site_settings`, so two sites sharing one database never see
 * (or overwrite) each other's gateway toggles, banners or SEO flags.
 */
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
     * Id of the site whose settings are active (1 = core, global table).
     */
    protected static function activeSiteId(): int
    {
        return (int) config('site.id', 1);
    }

    protected static function cacheKey(): string
    {
        $siteId = static::activeSiteId();

        return $siteId === 1 ? self::CACHE_KEY : self::CACHE_KEY . '_site_' . $siteId;
    }

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
        $siteId = static::activeSiteId();

        if ($siteId === 1) {
            $record = self::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        } else {
            SiteSetting::updateOrCreate(
                ['site_id' => $siteId, 'key' => $key],
                ['value' => $value]
            );
            $record = new self(['key' => $key, 'value' => $value]);
        }

        Cache::forget(static::cacheKey());

        return $record;
    }

    /**
     * Get all settings as an associative key => value array.
     */
    public static function allAsAssoc(): array
    {
        return Cache::remember(static::cacheKey(), 3600, function () {
            return static::freshAssoc();
        });
    }

    /**
     * All settings for the active site, read straight from the database (no cache).
     */
    public static function freshAssoc(): array
    {
        try {
            $siteId = static::activeSiteId();

            if ($siteId === 1) {
                return self::all()->pluck('value', 'key')->toArray();
            }

            return SiteSetting::where('site_id', $siteId)->pluck('value', 'key')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Clear the cached settings.
     */
    public static function clearCache(): void
    {
        Cache::forget(static::cacheKey());
    }
}
