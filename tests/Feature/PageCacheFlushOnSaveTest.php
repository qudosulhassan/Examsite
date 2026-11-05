<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PageCacheFlushOnSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_setting_bumps_the_page_cache_version(): void
    {
        Cache::forever('page_cache_version', 7);
        Setting::set('seo_google_verification_test', 'abc');
        $this->assertGreaterThan(7, (int) Cache::get('page_cache_version'));
    }
}
