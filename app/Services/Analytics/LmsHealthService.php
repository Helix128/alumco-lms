<?php

namespace App\Services\Analytics;

use App\Models\AdminAction;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\Feedback;
use App\Models\LmsHealthSnapshot;
use App\Models\Modulo;
use App\Models\NotificationDelivery;
use App\Models\SupportTicket;
use App\Models\SystemTaskRun;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Throwable;

class LmsHealthService
{
    /*
     * Problemas del panel anterior:
     * - Los KPI eran contadores sin contexto: no explicaban qué job falló, cuándo, con qué error ni qué acción ejecutar.
     * - No existía trending ni snapshots históricos, por lo que no se veía si el LMS mejoraba o empeoraba.
     * - Las alertas de configuración no eran accionables y ocultaban los registros específicos afectados.
     * - "Tareas recientes" no mostraba output, duración ni detalles útiles para depurar.
     * - No había señales de performance, estado de DB/Redis/colas/mail, logs de Laravel ni umbrales operativos.
     * - El panel era solo lectura: no permitía reintentar jobs, olvidar fallos ni limpiar caché con auditoría.
     * - No había búsqueda, filtro temporal ni filtros por severidad para investigar incidentes.
     */

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return [
            'jobs_fallidos' => DB::table('failed_jobs')->count(),
            'notificaciones_7d' => NotificationDelivery::where('sent_at', '>=', now()->subDays(7))->count(),
            'feedback_plataforma_nuevo' => Feedback::where('tipo', Feedback::TipoPlataforma)
                ->where('estado', Feedback::EstadoNuevo)
                ->count(),
            'tickets_abiertos' => SupportTicket::open()->count(),
            'tickets_criticos' => SupportTicket::open()
                ->where('priority', SupportTicket::PriorityCritical)
                ->count(),
            'tareas_recientes' => SystemTaskRun::query()
                ->orderByDesc('started_at')
                ->limit(8)
                ->get(),
            'alertas' => $this->alerts(),
        ];
    }

    /**
     * @return array{level: string, label: string, failed_jobs: int, pending_jobs: int, errors_last_hour: int, critical_alerts: int, trend: array<string, int>}
     */
    public function statusBar(): array
    {
        $failedJobs = DB::table('failed_jobs')->count();
        $pendingJobs = DB::table('jobs')->count();
        $errorsLastHour = $this->errorRate(1);
        $criticalAlerts = collect($this->configurationAlerts())->where('level', 'danger')->sum('count');

        $level = match (true) {
            $failedJobs > 0 || $errorsLastHour >= 5 || $criticalAlerts > 0 => 'danger',
            $pendingJobs >= 25 || $errorsLastHour > 0 => 'warning',
            default => 'ok',
        };

        return [
            'level' => $level,
            'label' => match ($level) {
                'danger' => 'Requiere atención',
                'warning' => 'Con advertencias',
                default => 'Operativo',
            },
            'failed_jobs' => $failedJobs,
            'pending_jobs' => $pendingJobs,
            'errors_last_hour' => $errorsLastHour,
            'critical_alerts' => (int) $criticalAlerts,
            'trend' => $this->trend(),
        ];
    }

    /**
     * @return array<int, array{name: string, status: string, detail: string, checked_at: string}>
     */
    public function servicesHealth(): array
    {
        return Cache::remember('lms-health:services', now()->addMinute(), function (): array {
            return [
                $this->checkDatabase(),
                $this->checkRedis(),
                $this->checkQueue(),
                $this->checkScheduler(),
                $this->checkMail(),
            ];
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function failedJobs(string $search = '', int $hours = 24): array
    {
        $query = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHours($hours))
            ->orderByDesc('failed_at')
            ->limit(25);

        if (trim($search) !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('uuid', 'like', "%{$search}%")
                    ->orWhere('queue', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%")
                    ->orWhere('exception', 'like', "%{$search}%");
            });
        }

        return $query->get()
            ->map(fn (object $job): array => $this->formatFailedJob($job))
            ->all();
    }

    /**
     * @return array<int, array{level: string, message: string, context: string, timestamp: string}>
     */
    public function errorLogs(string $level = 'all', string $search = ''): array
    {
        return Cache::remember(
            'lms-health:logs:'.md5($level.'|'.$search),
            now()->addSeconds(30),
            fn (): array => $this->parseLaravelLog($level, $search)
        );
    }

    /**
     * @return array{directory: string, total_bytes: int, total_human: string, file_count: int, archived_count: int, files: array<int, array{name: string, path: string, size_bytes: int, size_human: string, modified_at: string, is_current: bool}>}
     */
    public function logStorageSummary(): array
    {
        $files = collect($this->logFiles())
            ->map(fn (string $path): array => [
                'name' => basename($path),
                'path' => $path,
                'size_bytes' => File::size($path),
                'size_human' => $this->formatBytes(File::size($path)),
                'modified_at' => Carbon::createFromTimestamp(File::lastModified($path))->format('d/m/Y H:i'),
                'is_current' => basename($path) === 'laravel.log',
            ])
            ->sortByDesc('size_bytes')
            ->values()
            ->all();

        $totalBytes = (int) collect($files)->sum('size_bytes');

        return [
            'directory' => storage_path('logs'),
            'total_bytes' => $totalBytes,
            'total_human' => $this->formatBytes($totalBytes),
            'file_count' => count($files),
            'archived_count' => collect($files)->where('is_current', false)->count(),
            'files' => $files,
        ];
    }

    public function deleteLogFile(string $fileName, User $user): bool
    {
        $path = $this->validatedLogPath($fileName);

        if (! $path || ! File::exists($path)) {
            return false;
        }

        $this->runAuditedAction(
            $user,
            'logs:delete-file',
            'log_file',
            basename($path),
            function () use ($path): int {
                File::delete($path);

                return 0;
            }
        );

        $this->forgetLogCaches();

        return true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function configurationAlerts(): array
    {
        return Cache::remember('lms-health:config-alerts', now()->addMinutes(2), function (): array {
            return [
                $this->courseAlert('cursos_sin_modulos', 'Cursos sin módulos', 'warning', Curso::doesntHave('modulos')),
                $this->courseAlert('cursos_sin_audiencia', 'Cursos sin audiencia', 'warning', Curso::doesntHave('estamentos')),
                $this->courseAlert('cursos_sin_planificacion', 'Cursos sin planificación', 'warning', Curso::doesntHave('planificaciones')),
                $this->evaluationAlert(),
                $this->moduleAlert(),
                $this->userAlert(),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function databaseStats(): array
    {
        return Cache::remember('lms-health:database-stats', now()->addMinutes(2), function (): array {
            $tables = collect(DB::select('SHOW TABLE STATUS'))
                ->map(fn (object $table): array => [
                    'name' => $table->Name,
                    'rows' => (int) ($table->Rows ?? 0),
                    'data_mb' => round((int) ($table->Data_length ?? 0) / 1024 / 1024, 2),
                    'index_mb' => round((int) ($table->Index_length ?? 0) / 1024 / 1024, 2),
                    'size_mb' => round(((int) ($table->Data_length ?? 0) + (int) ($table->Index_length ?? 0)) / 1024 / 1024, 2),
                    'updated_at' => $table->Update_time ?? null,
                ])
                ->sortByDesc('size_mb')
                ->values()
                ->all();

            return [
                'connection' => config('database.default'),
                'driver' => config('database.connections.'.config('database.default').'.driver'),
                'database' => config('database.connections.'.config('database.default').'.database'),
                'table_count' => count($tables),
                'total_rows' => collect($tables)->sum('rows'),
                'total_size_mb' => round(collect($tables)->sum('size_mb'), 2),
                'data_size_mb' => round(collect($tables)->sum('data_mb'), 2),
                'index_size_mb' => round(collect($tables)->sum('index_mb'), 2),
                'largest_table' => $tables[0]['name'] ?? 'N/D',
                'pending_jobs' => DB::table('jobs')->count(),
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'tables' => array_slice($tables, 0, 12),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function cacheStats(): array
    {
        $summary = Cache::remember('lms-health:cache-summary', now()->addMinute(), function (): array {
            $store = config('cache.default');
            $rows = DB::getSchemaBuilder()->hasTable('cache') ? DB::table('cache')->count() : null;

            return [
                'store' => $store,
                'driver' => config("cache.stores.{$store}.driver"),
                'prefix' => config('cache.prefix'),
                'database_keys' => $rows,
                'active_users' => (int) Cache::get('active_users_count', 0),
            ];
        });

        $snapshots = LmsHealthSnapshot::query()
            ->latest('captured_at')
            ->take(12)
            ->get(['id', 'failed_jobs_count', 'pending_jobs_count', 'error_rate', 'active_users', 'captured_at']);

        return [
            ...$summary,
            'snapshot_count' => $snapshots->count(),
            'latest_snapshot_at' => $snapshots->first()?->captured_at?->format('d/m H:i') ?? 'Sin datos',
            'avg_error_rate' => round($snapshots->avg(fn (LmsHealthSnapshot $snapshot): float => (float) $snapshot->error_rate) ?? 0, 2),
            'max_pending_jobs' => $snapshots->max('pending_jobs_count') ?? 0,
            'max_failed_jobs' => $snapshots->max('failed_jobs_count') ?? 0,
            'avg_active_users' => round($snapshots->avg('active_users') ?? 0, 1),
            'snapshots' => $snapshots->map(fn (LmsHealthSnapshot $snapshot): array => [
                'id' => $snapshot->id,
                'captured_at_key' => $snapshot->captured_at?->timestamp,
                'captured_at_display' => $snapshot->captured_at?->format('d/m H:i'),
                'failed_jobs_count' => $snapshot->failed_jobs_count,
                'pending_jobs_count' => $snapshot->pending_jobs_count,
                'error_rate' => $snapshot->error_rate,
                'active_users' => $snapshot->active_users,
            ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<int, AdminAction>
     */
    public function recentAdminActions(): array
    {
        return AdminAction::query()
            ->with('user:id,name')
            ->latest('executed_at')
            ->limit(8)
            ->get()
            ->all();
    }

    public function retryFailedJob(string $uuid, User $user): void
    {
        $this->runAuditedAction($user, 'queue:retry', 'failed_job', $uuid, fn (): int => Artisan::call('queue:retry', ['id' => [$uuid]]));
    }

    public function forgetFailedJob(string $uuid, User $user): void
    {
        $this->runAuditedAction($user, 'queue:forget', 'failed_job', $uuid, fn (): int => Artisan::call('queue:forget', ['id' => $uuid]));
    }

    public function clearOptimizedCache(User $user): void
    {
        $this->runAuditedAction($user, 'optimize:clear', null, null, fn (): int => Artisan::call('optimize:clear'));
    }

    public function flushFailedJobs(User $user): void
    {
        $this->runAuditedAction($user, 'queue:flush', 'failed_jobs', '*', fn (): int => Artisan::call('queue:flush'));
    }

    public function getErrorRate(int $hours): int
    {
        return $this->errorRate($hours);
    }

    /**
     * @return array<int, array{label: string, value: int, level: string}>
     */
    private function alerts(): array
    {
        return [
            [
                'label' => 'Cursos sin módulos',
                'value' => Curso::doesntHave('modulos')->count(),
                'level' => 'warning',
            ],
            [
                'label' => 'Cursos sin audiencia',
                'value' => Curso::doesntHave('estamentos')->count(),
                'level' => 'warning',
            ],
            [
                'label' => 'Cursos sin planificación',
                'value' => Curso::doesntHave('planificaciones')->count(),
                'level' => 'warning',
            ],
            [
                'label' => 'Evaluaciones sin preguntas',
                'value' => Evaluacion::doesntHave('preguntas')->count(),
                'level' => 'danger',
            ],
            [
                'label' => 'Módulos de archivo incompletos',
                'value' => Modulo::whereIn('tipo_contenido', ['video', 'pdf', 'ppt', 'imagen'])
                    ->whereNull('ruta_archivo')
                    ->count(),
                'level' => 'danger',
            ],
            [
                'label' => 'Usuarios activos sin sede o estamento',
                'value' => User::where('activo', true)
                    ->where(fn ($query) => $query->whereNull('sede_id')->orWhereNull('estamento_id'))
                    ->count(),
                'level' => 'warning',
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function trend(): array
    {
        $latest = LmsHealthSnapshot::query()->latest('captured_at')->first();
        $previous = LmsHealthSnapshot::query()
            ->when($latest, fn ($query) => $query->where('id', '<', $latest->id))
            ->latest('captured_at')
            ->first();

        return [
            'failed_jobs_delta' => $latest && $previous ? $latest->failed_jobs_count - $previous->failed_jobs_count : 0,
            'pending_jobs_delta' => $latest && $previous ? $latest->pending_jobs_count - $previous->pending_jobs_count : 0,
            'error_rate_delta' => $latest && $previous ? (int) ($latest->error_rate - $previous->error_rate) : 0,
        ];
    }

    /**
     * @return array{name: string, status: string, detail: string, checked_at: string}
     */
    private function checkDatabase(): array
    {
        return $this->checkService('Base de datos', function (): string {
            DB::select('select 1');

            return 'Conexión '.config('database.default').' operativa';
        });
    }

    /**
     * @return array{name: string, status: string, detail: string, checked_at: string}
     */
    private function checkRedis(): array
    {
        return $this->checkService('Redis', function (): string {
            Redis::connection()->ping();

            return 'Redis responde a ping';
        });
    }

    /**
     * @return array{name: string, status: string, detail: string, checked_at: string}
     */
    private function checkQueue(): array
    {
        return $this->checkService('Colas', function (): string {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            if ($failed > 0) {
                throw new \RuntimeException("{$failed} jobs fallidos, {$pending} pendientes");
            }

            return "{$pending} jobs pendientes";
        });
    }

    /**
     * @return array{name: string, status: string, detail: string, checked_at: string}
     */
    private function checkScheduler(): array
    {
        return $this->checkService('Scheduler', function (): string {
            $lastRun = SystemTaskRun::query()->latest('started_at')->first();

            if (! $lastRun) {
                return 'Sin ejecuciones registradas todavía';
            }

            return 'Última tarea '.$lastRun->command.' '.$lastRun->started_at->diffForHumans();
        });
    }

    /**
     * @return array{name: string, status: string, detail: string, checked_at: string}
     */
    private function checkMail(): array
    {
        return $this->checkService('Mail', function (): string {
            Mail::mailer()->getSymfonyTransport();

            return 'Mailer '.config('mail.default').' configurado';
        });
    }

    /**
     * @return array{name: string, status: string, detail: string, checked_at: string}
     */
    private function checkService(string $name, callable $callback): array
    {
        try {
            $detail = $callback();

            return ['name' => $name, 'status' => 'ok', 'detail' => $detail, 'checked_at' => now()->format('H:i:s')];
        } catch (Throwable $exception) {
            return ['name' => $name, 'status' => 'danger', 'detail' => $exception->getMessage(), 'checked_at' => now()->format('H:i:s')];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatFailedJob(object $job): array
    {
        $payload = json_decode($job->payload, true) ?: [];
        $displayName = $payload['displayName'] ?? $payload['job'] ?? 'Job desconocido';
        $exceptionLines = preg_split('/\R/', (string) $job->exception) ?: [];

        return [
            'id' => (int) $job->id,
            'uuid' => (string) $job->uuid,
            'connection' => (string) $job->connection,
            'queue' => (string) $job->queue,
            'display_name' => Str::afterLast((string) $displayName, '\\'),
            'failed_at' => $job->failed_at,
            'error' => Str::limit($exceptionLines[0] ?? 'Sin excepción registrada', 220),
            'payload' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'exception' => (string) $job->exception,
            'max_tries' => $payload['maxTries'] ?? null,
            'timeout' => $payload['timeout'] ?? null,
        ];
    }

    /**
     * @return array<int, array{level: string, message: string, context: string, timestamp: string}>
     */
    private function parseLaravelLog(string $level, string $search): array
    {
        $path = storage_path('logs/laravel.log');

        if (! is_file($path)) {
            return [];
        }

        $size = filesize($path) ?: 0;
        $handle = fopen($path, 'rb');

        if (! $handle) {
            return [];
        }

        fseek($handle, max(0, $size - 524288));
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        preg_match_all('/\[(?<timestamp>[^\]]+)\]\s+(?<env>\w+)\.(?<level>\w+):\s+(?<message>.*?)(?=\n\[[^\]]+\]\s+\w+\.\w+:|\z)/s', $content, $matches, PREG_SET_ORDER);

        return collect($matches)
            ->reverse()
            ->map(fn (array $match): array => [
                'timestamp' => $match['timestamp'],
                'level' => strtolower($match['level']),
                'message' => trim(Str::before($match['message'], "\n")),
                'context' => trim(Str::after($match['message'], "\n")),
            ])
            ->filter(fn (array $entry): bool => $level === 'all' || $entry['level'] === $level)
            ->filter(fn (array $entry): bool => trim($search) === '' || str_contains(mb_strtolower($entry['message'].' '.$entry['context']), mb_strtolower($search)))
            ->take(50)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function logFiles(): array
    {
        $directory = storage_path('logs');

        if (! File::isDirectory($directory)) {
            return [];
        }

        return File::glob($directory.DIRECTORY_SEPARATOR.'*.log') ?: [];
    }

    private function validatedLogPath(string $fileName): ?string
    {
        $baseName = basename($fileName);

        if ($baseName !== $fileName || ! str_ends_with($baseName, '.log')) {
            return null;
        }

        $path = storage_path('logs'.DIRECTORY_SEPARATOR.$baseName);
        $realDirectory = realpath(storage_path('logs'));
        $realPath = realpath($path);

        if (! $realDirectory || ! $realPath || ! str_starts_with($realPath, $realDirectory.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $realPath;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / 1024 / 1024 / 1024, 2).' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    private function forgetLogCaches(): void
    {
        Cache::forget('lms-health:logs:'.md5('all|'));
        Cache::forget('lms-health:logs:'.md5('error|'));
        Cache::forget('lms-health:logs:'.md5('warning|'));
        Cache::forget('lms-health:logs:'.md5('info|'));
    }

    /**
     * @param  Builder<Curso>  $query
     * @return array<string, mixed>
     */
    private function courseAlert(string $key, string $label, string $level, Builder $query): array
    {
        $records = (clone $query)
            ->with('capacitador:id,name')
            ->latest('updated_at')
            ->limit(6)
            ->get(['id', 'titulo', 'capacitador_id', 'updated_at'])
            ->map(fn (Curso $curso): array => [
                'id' => $curso->id,
                'label' => $curso->titulo,
                'detail' => 'Capacitador: '.($curso->capacitador?->name ?? 'Sin asignar'),
                'url' => route('capacitador.cursos.editar', $curso),
            ])
            ->all();

        return ['key' => $key, 'label' => $label, 'level' => $level, 'count' => $query->count(), 'records' => $records];
    }

    /**
     * @return array<string, mixed>
     */
    private function evaluationAlert(): array
    {
        $query = Evaluacion::doesntHave('preguntas')->with('modulo.curso:id,titulo');

        return [
            'key' => 'evaluaciones_sin_preguntas',
            'label' => 'Evaluaciones sin preguntas',
            'level' => 'danger',
            'count' => $query->count(),
            'records' => $query->limit(6)->get()->map(fn (Evaluacion $evaluacion): array => [
                'id' => $evaluacion->id,
                'label' => 'Evaluación #'.$evaluacion->id,
                'detail' => $evaluacion->modulo?->curso?->titulo ?? 'Curso no disponible',
                'url' => $evaluacion->modulo?->curso ? route('capacitador.cursos.modulos.evaluacion', [$evaluacion->modulo->curso, $evaluacion->modulo]) : null,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function moduleAlert(): array
    {
        $query = Modulo::whereIn('tipo_contenido', ['video', 'pdf', 'ppt', 'imagen'])
            ->whereNull('ruta_archivo')
            ->with('curso:id,titulo');

        return [
            'key' => 'modulos_archivo_incompleto',
            'label' => 'Módulos de archivo incompletos',
            'level' => 'danger',
            'count' => $query->count(),
            'records' => $query->limit(6)->get(['id', 'curso_id', 'titulo', 'tipo_contenido'])->map(fn (Modulo $modulo): array => [
                'id' => $modulo->id,
                'label' => $modulo->titulo,
                'detail' => 'Tipo: '.$modulo->tipo_contenido.' · '.($modulo->curso?->titulo ?? 'Sin curso'),
                'url' => $modulo->curso ? route('capacitador.cursos.modulos.editar', [$modulo->curso, $modulo]) : null,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function userAlert(): array
    {
        $query = User::where('activo', true)
            ->where(fn ($query) => $query->whereNull('sede_id')->orWhereNull('estamento_id'));

        return [
            'key' => 'usuarios_sin_sede_estamento',
            'label' => 'Usuarios activos sin sede o estamento',
            'level' => 'warning',
            'count' => $query->count(),
            'records' => $query->limit(6)->get(['id', 'name', 'email', 'sede_id', 'estamento_id'])->map(fn (User $user): array => [
                'id' => $user->id,
                'label' => $user->name,
                'detail' => $user->email,
                'url' => route('admin.usuarios.index'),
            ])->all(),
        ];
    }

    private function errorRate(int $hours): int
    {
        return collect($this->parseLaravelLog('all', ''))
            ->filter(fn (array $entry): bool => in_array($entry['level'], ['error', 'critical', 'alert', 'emergency'], true))
            ->filter(fn (array $entry): bool => rescue(fn () => Carbon::parse($entry['timestamp'])->gte(now()->subHours($hours)), false))
            ->count();
    }

    private function runAuditedAction(User $user, string $action, ?string $targetType, ?string $targetId, callable $callback): void
    {
        $status = 'success';

        try {
            $exitCode = $callback();
            $output = Artisan::output();

            if ($exitCode !== 0) {
                $status = 'failed';
            }
        } catch (Throwable $exception) {
            $status = 'failed';
            $output = $exception->getMessage();
            Log::error('Acción administrativa fallida en Salud LMS.', ['action' => $action, 'exception' => $exception]);
        }

        AdminAction::create([
            'user_id' => $user->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'status' => $status,
            'output' => Str::limit($output ?? '', 5000),
            'metadata' => ['ip' => request()->ip()],
            'executed_at' => now(),
        ]);

        Cache::forget('lms-health:services');
    }
}
