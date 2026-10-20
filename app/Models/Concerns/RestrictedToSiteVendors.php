<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Limits a model to the vendors this site is allowed to show (config 'site.vendors', set with
 * SITE_VENDORS=microsoft in .env). With no vendors configured nothing changes.
 *
 * It is a global scope, so every list, search, sitemap, relation and route lookup follows it:
 * an exam of another vendor simply does not exist on a vendor-restricted site (404 by URL).
 * Use withoutGlobalScope('site_vendors') for deliberate cross-vendor work.
 */
trait RestrictedToSiteVendors
{
    /** @var array<string, array<int>> */
    protected static array $siteVendorIdCache = [];

    public static function bootRestrictedToSiteVendors(): void
    {
        static::addGlobalScope('site_vendors', function (Builder $builder) {
            $ids = static::allowedSiteVendorIds();

            if ($ids === null) {
                return;
            }

            $builder->whereIn($builder->getModel()->qualifyColumn(static::siteVendorColumn()), $ids);
        });
    }

    /**
     * Column on this model that holds the vendor id ('vendor_id', or 'id' on Vendor itself).
     */
    protected static function siteVendorColumn(): string
    {
        return 'vendor_id';
    }

    /**
     * @return array<int>|null null = no restriction. An empty-looking restriction (vendor slug
     *                         not in the database) matches nothing rather than everything.
     */
    public static function allowedSiteVendorIds(): ?array
    {
        $slugs = array_values(array_filter((array) config('site.vendors', [])));

        if ($slugs === []) {
            return null;
        }

        $key = implode(',', $slugs);

        if (isset(static::$siteVendorIdCache[$key])) {
            return static::$siteVendorIdCache[$key];
        }

        $ids = DB::table('vendors')->whereIn('slug', $slugs)->pluck('id')->map(fn ($id) => (int) $id)->all();

        // Only cache a real answer, so a vendor created later is picked up.
        if ($ids !== []) {
            static::$siteVendorIdCache[$key] = $ids;
        }

        return $ids === [] ? [0] : $ids;
    }

    public static function flushSiteVendorCache(): void
    {
        static::$siteVendorIdCache = [];
    }
}
