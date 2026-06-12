<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduled Tasks ───────────────────────────────────────────────────────────

// Aggregate yesterday's stats every day at 00:05
Schedule::command('analytics:aggregate-daily')->dailyAt('00:05');

// Check for low-stock products every morning at 08:00
Schedule::command('inventory:check-low-stock')->dailyAt('08:00');

// Hard-delete soft-deleted records past retention period (weekly, Sunday 03:00)
Schedule::command('model:prune')->weeklyOn(0, '03:00');

// Delete app notifications older than 3 days, daily at 03:30
Schedule::command('notifications:prune')->dailyAt('03:30');

// Horizon metrics snapshot every 5 minutes (powers the dashboard graphs)
Schedule::command('horizon:snapshot')->everyFiveMinutes()->runInBackground();
