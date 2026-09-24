<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refuse to run against a real database.
     *
     * A cached config (bootstrap/cache/config.php from `php artisan config:cache` / `optimize`)
     * overrides phpunit.xml, and RefreshDatabase would then wipe the real database.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            fwrite(STDERR, "\nTests stopped: they would run on the '{$connection}' database '{$database}' instead of the in-memory test database.\n"
                . "Run `php artisan config:clear` first (a cached config overrides phpunit.xml).\n\n");
            exit(1);
        }

        return $app;
    }
}
