<?php

use App\Models\LmsHealthSnapshot;
use App\Services\Analytics\LmsHealthService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

Schedule::call(function (): void {
    LmsHealthSnapshot::create([
        'failed_jobs_count' => DB::table('failed_jobs')->count(),
        'pending_jobs_count' => DB::table('jobs')->count(),
        'error_rate' => app(LmsHealthService::class)->getErrorRate(1),
        'active_users' => Cache::get('active_users_count', 0),
        'captured_at' => now(),
    ]);
})->everyFiveMinutes();
