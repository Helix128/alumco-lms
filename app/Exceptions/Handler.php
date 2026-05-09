<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Spatie\LaravelFlare\Facades\Flare;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $exception): void {
            $traceId = $this->resolveTraceId();

            $this->reportToMonitoring($exception, $traceId);

            $request = request();

            Log::error('Unhandled exception', [
                'trace_id' => $traceId,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);
        });

        $this->renderable(function (ValidationException $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $traceId = $this->resolveTraceId();

            return response()->json([
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $exception->errors(),
                'trace_id' => $traceId,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (Throwable $exception, Request $request) {
            if ($exception instanceof AuthenticationException && ! $request->expectsJson()) {
                return null;
            }

            $traceId = $this->resolveTraceId();
            [$statusCode, $message] = $this->mapExceptionToStatusAndMessage($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => $statusCode,
                        'message' => $message,
                        'trace_id' => $traceId,
                    ],
                ], $statusCode);
            }

            return response()->view('errors.generic', [
                'traceId' => $traceId,
                'statusCode' => $statusCode,
            ], $statusCode);
        });
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function mapExceptionToStatusAndMessage(Throwable $exception): array
    {
        return match (true) {
            $exception instanceof ModelNotFoundException => [Response::HTTP_NOT_FOUND, 'Recurso no encontrado.'],
            $exception instanceof AuthenticationException => [Response::HTTP_UNAUTHORIZED, 'No autenticado.'],
            $exception instanceof AuthorizationException => [Response::HTTP_FORBIDDEN, 'No tienes permiso para realizar esta acción.'],
            $exception instanceof HttpExceptionInterface => [
                $exception->getStatusCode(),
                $exception->getStatusCode() === Response::HTTP_NOT_FOUND
                    ? 'Recurso no encontrado.'
                    : ($exception->getMessage() ?: Response::$statusTexts[$exception->getStatusCode()] ?? 'Error HTTP.'),
            ],
            default => [Response::HTTP_INTERNAL_SERVER_ERROR, 'Ocurrió un error inesperado. Si el problema persiste, contacta a soporte.'],
        };
    }

    private function resolveTraceId(): string
    {
        if (app()->bound('exception.trace_id')) {
            return (string) app('exception.trace_id');
        }

        $traceId = substr(md5(uniqid('', true)), 0, 8);
        app()->instance('exception.trace_id', $traceId);

        return $traceId;
    }

    private function reportToMonitoring(Throwable $exception, string $traceId): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (class_exists(Integration::class)) {
            if (function_exists('\Sentry\configureScope')) {
                \Sentry\configureScope(function ($scope) use ($traceId): void {
                    if (method_exists($scope, 'setTag')) {
                        $scope->setTag('trace_id', $traceId);
                    }
                });
            }

            Integration::captureUnhandledException($exception);

            return;
        }

        if (class_exists(Flare::class)) {
            Flare::context('trace_id', $traceId);

            if (method_exists(Flare::class, 'report')) {
                Flare::report($exception);
            }
        }
    }
}
