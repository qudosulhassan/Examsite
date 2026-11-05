<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FlushPageCache extends Command
{
    protected $signature = 'pagecache:flush';

    protected $description = 'Forget every cached public page (bumps the page cache version; old entries expire on their own)';

    public function handle(): int
    {
        $version = (int) Cache::get('page_cache_version', 1) + 1;
        Cache::forever('page_cache_version', $version);
        $this->info("Page cache version is now {$version}.");

        return self::SUCCESS;
    }
}
