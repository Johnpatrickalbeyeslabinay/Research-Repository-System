<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Automatic Database Backup
|--------------------------------------------------------------------------
|
| Automatically creates a PostgreSQL database backup every day at 11:00 PM.
|
*/

Schedule::command('backup:database')
    ->dailyAt('23:00')
    ->withoutOverlapping();
