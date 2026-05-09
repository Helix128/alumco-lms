<?php

use App\Exceptions\Handler as AppExceptionHandler;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureCapacitadorAccess;
use App\Http\Middleware\EnsureCapacitadorInternoAccess;
use App\Http\Middleware\EnsureWorkerAreaAccess;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Exceptions\Handler as FoundationExceptionHandler;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->alias([
            'admin' => EnsureAdminAccess::class,
            'capacitador' => EnsureCapacitadorAccess::class,
            'capacitador.interno' => EnsureCapacitadorInternoAccess::class,
            'worker.area' => EnsureWorkerAreaAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSingletons([
        ExceptionHandlerContract::class => AppExceptionHandler::class,
        FoundationExceptionHandler::class => AppExceptionHandler::class,
    ])->create();
