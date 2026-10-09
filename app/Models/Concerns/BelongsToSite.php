<?php

namespace App\Models\Concerns;

use App\Services\SiteContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scopes a model to the website the app is currently serving, so that two sites
 * can share one database without sharing customers or coupons.
 *
 * Reads are filtered by `site_id`, new rows get the current `site_id`.
 * Use withoutGlobalScope('site') for deliberate cross-site queries.
 */
trait BelongsToSite
{
    public static function bootBelongsToSite(): void
    {
        static::addGlobalScope('site', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable() . '.site_id', static::currentSiteId());
        });

        static::creating(function ($model) {
            if (empty($model->site_id)) {
                $model->site_id = static::currentSiteId();
            }
        });
    }

    public static function currentSiteId(): int
    {
        if (config('site.mode', 'standalone') === 'standalone') {
            return (int) config('site.id', 1);
        }

        return (int) (app(SiteContext::class)->id() ?? config('site.id', 1));
    }
}
