<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily check for expiring ingredients (runs at 9 AM UTC)
Schedule::command('ingredients:check-expiring')
    ->dailyAt('09:00')
    ->timezone('UTC')
    ->withoutOverlapping();
