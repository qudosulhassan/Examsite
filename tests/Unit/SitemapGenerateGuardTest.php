<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SitemapGenerateGuardTest extends TestCase
{
    public function test_other_sites_never_write_static_sitemap_files(): void
    {
        config(['site.id' => 3]);
        $before = glob(public_path('sitemap*.xml'));
        $mtimes = array_map('filemtime', $before);

        $this->assertSame(0, Artisan::call('sitemap:generate'));

        $after = glob(public_path('sitemap*.xml'));
        $this->assertSame($before, $after, 'no files created or removed');
        $this->assertSame($mtimes, array_map('filemtime', $after), 'no files rewritten');
    }
}
