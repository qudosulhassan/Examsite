<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('sitemap:generate')->dailyAt('02:00');

// Drains the mail/queue jobs (order confirmations, welcome emails, demo PDFs) every
// minute. Runs for up to 50s so it doesn't overlap the next minute's cron tick, and
// stops early once the queue is empty. Needs QUEUE_CONNECTION=database in .env.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
