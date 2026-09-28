<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Schedule::command()/->runInBackground() shell out via Symfony Process, which needs
// proc_open(). This host has proc_open disabled (standard shared-hosting hardening),
// so every scheduled command must run in-process via Schedule::call()+Artisan::call()
// instead, never Schedule::command().

Schedule::call(fn () => Artisan::call('sitemap:generate'))->dailyAt('02:00');

// Drains the mail/queue jobs (order confirmations, welcome emails, demo PDFs) every
// minute. Runs for up to 50s so it doesn't overlap the next minute's cron tick, and
// stops early once the queue is empty. Needs QUEUE_CONNECTION=database in .env.
Schedule::call(fn () => Artisan::call('queue:work', [
    '--stop-when-empty' => true,
    '--max-time' => 50,
    '--tries' => 3,
]))->name('drain-queue')->everyMinute()->withoutOverlapping();
