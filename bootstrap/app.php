<?php

use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureCapacitadorAccess;
use App\Http\Middleware\EnsureCapacitadorInternoAccess;
use App\Http\Middleware\EnsureWorkerAreaAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdminAccess::class,
            'capacitador' => EnsureCapacitadorAccess::class,
            'capacitador.interno' => EnsureCapacitadorInternoAccess::class,
            'worker.area' => EnsureWorkerAreaAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
