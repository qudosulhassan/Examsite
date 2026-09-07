<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = [
        'old_url',
        'new_url',
        'status_code',
        'hits_count',
        'is_active',
        'last_accessed_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'hits_count' => 'integer',
        'is_active' => 'boolean',
        'last_accessed_at' => 'datetime',
    ];

    /**
     * Normalize URL path for consistent lookup.
     */
    public static function normalizeUrl(string $url): string
    {
        $url = trim($url);
        // If absolute URL for the same host, extract path
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $parsed = parse_url($url);
            $url = ($parsed['path'] ?? '/') . (!empty($parsed['query']) ? '?' . $parsed['query'] : '');
        }
        
        // Ensure leading slash for consistency
        if (!str_starts_with($url, '/')) {
            $url = '/' . $url;
        }

        // Strip trailing slash unless root
        if (strlen($url) > 1 && str_ends_with($url, '/')) {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    /**
     * Check if creating or updating this redirect will form a redirect loop.
     */
    public static function wouldCauseLoop(string $source, string $destination, ?int $excludeId = null): bool
    {
        $srcNorm = self::normalizeUrl($source);
        $dstNorm = self::normalizeUrl($destination);

        if ($srcNorm === $dstNorm) {
            return true;
        }

        // Trace redirect chain up to 10 hops
        $visited = [$srcNorm, $dstNorm];
        $current = $dstNorm;

        for ($i = 0; $i < 10; $i++) {
            $query = self::where('is_active', true)
                ->where(function ($q) use ($current) {
                    $q->where('old_url', $current)
                      ->orWhere('old_url', ltrim($current, '/'))
                      ->orWhere('old_url', '/' . ltrim($current, '/'));
                });

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $next = $query->first();
            if (!$next) {
                return false; // Safe, chain terminates
            }

            $nextDest = self::normalizeUrl($next->new_url);
            if (in_array($nextDest, $visited, true)) {
                return true; // Loop detected!
            }

            $visited[] = $nextDest;
            $current = $nextDest;
        }

        return false;
    }
}
