<?php

namespace App\Providers;

use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureAdminOrDeveloperAccess;
use App\Http\Middleware\EnsureCapacitadorAccess;
use App\Http\Middleware\EnsureCapacitadorInternoAccess;
use App\Http\Middleware\EnsureDeveloperAccess;
use App\Http\Middleware\EnsureWorkerAreaAccess;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewLmsHealth', fn ($user): bool => $user->isDesarrollador());

        Livewire::addPersistentMiddleware([
            EnsureAdminAccess::class,
            EnsureAdminOrDeveloperAccess::class,
            EnsureCapacitadorAccess::class,
            EnsureCapacitadorInternoAccess::class,
            EnsureDeveloperAccess::class,
            EnsureWorkerAreaAccess::class,
        ]);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').$request->ip());
        });
    }
}
