<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Checks stock levels once a day and emails Admin/Procurement Staff
// about anything at or below its reorder level. Needs the Laravel
// scheduler running (`php artisan schedule:work` in dev, a cron entry
// calling `schedule:run` every minute in production) and a working
// MAIL_MAILER in .env, or notifications will just silently queue/fail.
Schedule::command('inventory:check-low-stock')->dailyAt('07:00');
