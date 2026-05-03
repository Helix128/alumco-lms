<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('lms:send-course-available-notifications')
    ->dailyAt('08:00')
    ->timezone('America/Santiago')
    ->withoutOverlapping();

Schedule::command('lms:send-course-deadline-reminders')
    ->dailyAt('09:00')
    ->timezone('America/Santiago')
    ->withoutOverlapping();
