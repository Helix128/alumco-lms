<?php

namespace Tests\Feature;

use App\Livewire\Developer\SaludLms\CacheStatsPanel;
use App\Livewire\Developer\SaludLms\DatabaseStatsPanel;
use App\Livewire\Developer\SaludLms\ErrorLogsPanel;
use App\Livewire\Developer\SaludLms\JobsPanel;
use App\Livewire\Developer\SaludLms\QuickActionsPanel;
use App\Models\AdminAction;
use App\Models\LmsHealthSnapshot;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class LmsHealthDashboardTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_developer_can_open_actionable_lms_health_dashboard(): void
    {
        $developer = $this->createDev();

        $this->actingAs($developer)
            ->get(route('dev.salud-lms'))
            ->assertOk()
            ->assertSee('Salud operacional del LMS')
            ->assertSee('Estado de servicios')
            ->assertSee('Alertas de configuración')
            ->assertSee('Datos y performance')
            ->assertDontSee('Vista dedicada para lectura tail');
    }

    public function test_developer_can_open_dedicated_logs_view(): void
    {
        $developer = $this->createDev();

        $this->actingAs($developer)
            ->get(route('dev.salud-lms', ['vista' => 'logs']))
            ->assertOk()
            ->assertSee('Logs Laravel')
            ->assertSee('Revisa entradas recientes')
            ->assertSee('copia el stack trace exacto')
            ->assertSee('Uso en disco')
            ->assertSee('Borrar')
            ->assertDontSee('Copiar logs completos')
            ->assertDontSee('Borrar archivados')
            ->assertDontSee('Vaciar laravel.log');
    }

    public function test_developer_can_copy_a_specific_stacktrace_from_health_panel(): void
    {
        $developer = $this->createDev();
        $path = storage_path('logs/laravel.log');
        $backup = File::exists($path) ? File::get($path) : null;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, "[2026-05-09 10:00:00] local.ERROR: RuntimeException: Falla depurable\nStack trace:\n#0 /var/www/html/app/Demo.php(10): demo()\n#1 {main}");
        Cache::forget('lms-health:logs:'.md5('all|'));

        try {
            Livewire::actingAs($developer)
                ->test(ErrorLogsPanel::class)
                ->assertSee('RuntimeException: Falla depurable')
                ->assertSee('Copiar stack trace')
                ->assertSee('Stack trace:')
                ->assertSee('/var/www/html/app/Demo.php');
        } finally {
            if ($backup === null) {
                File::delete($path);
            } else {
                File::put($path, $backup);
            }

            Cache::forget('lms-health:logs:'.md5('all|'));
        }
    }

    public function test_developer_can_delete_a_specific_archived_log(): void
    {
        $developer = $this->createDev();
        $path = storage_path('logs/lms-health-delete-test.log');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'Log temporal para borrar');

        Livewire::actingAs($developer)
            ->test(ErrorLogsPanel::class)
            ->assertSee('lms-health-delete-test.log')
            ->call('requestDeleteFile', 'lms-health-delete-test.log')
            ->assertSee('Confirmación requerida')
            ->call('confirmPendingAction')
            ->assertSet('status', 'Log eliminado correctamente.');

        $this->assertFalse(File::exists($path));
        $this->assertDatabaseHas('admin_actions', [
            'user_id' => $developer->id,
            'action' => 'logs:delete-file',
            'target_id' => 'lms-health-delete-test.log',
        ]);
    }

    public function test_non_developer_cannot_open_lms_health_dashboard(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('dev.salud-lms'))
            ->assertForbidden();
    }

    public function test_jobs_panel_shows_failed_job_debug_context(): void
    {
        $developer = $this->createDev();

        DB::table('failed_jobs')->insert([
            'uuid' => 'failed-job-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => 'App\\Jobs\\SendExternalApiMessage',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'maxTries' => 3,
                'timeout' => 60,
            ], JSON_THROW_ON_ERROR),
            'exception' => "RuntimeException: API externa caída\nStack trace completo",
            'failed_at' => now(),
        ]);

        Livewire::actingAs($developer)
            ->test(JobsPanel::class)
            ->assertSee('SendExternalApiMessage')
            ->assertSee('API externa caída')
            ->assertSee('failed-job-uuid')
            ->call('requestRetry', 'failed-job-uuid')
            ->assertSee('Reintentar job fallido')
            ->assertSee('Confirmación requerida');
    }

    public function test_cache_stats_panel_renders_snapshots_without_cached_models(): void
    {
        $developer = $this->createDev();

        Cache::put('lms-health:cache-stats', [
            'snapshots' => ['snapshot-corrupto'],
        ]);

        LmsHealthSnapshot::create([
            'failed_jobs_count' => 2,
            'pending_jobs_count' => 5,
            'error_rate' => 1.25,
            'active_users' => 7,
            'captured_at' => now()->setTime(9, 30),
        ]);

        Livewire::actingAs($developer)
            ->test(CacheStatsPanel::class)
            ->assertSee('Fallidos: 2')
            ->assertSee('Pendientes: 5')
            ->assertSee('Errores: 1.25');
    }

    public function test_database_stats_panel_shows_detailed_size_metrics(): void
    {
        $developer = $this->createDev();

        Livewire::actingAs($developer)
            ->test(DatabaseStatsPanel::class)
            ->assertSee('Tamaño')
            ->assertSee('Data / índices')
            ->assertSee('Tablas / filas')
            ->assertDontSee('Motor');
    }

    public function test_quick_actions_are_audited(): void
    {
        $developer = $this->createDev();

        Livewire::actingAs($developer)
            ->test(QuickActionsPanel::class)
            ->call('requestAction', 'clear_cache')
            ->assertSee('Confirmación requerida')
            ->assertSee('Limpiar caché optimizada')
            ->call('confirmPendingAction')
            ->assertSee('Caché de framework limpiada');

        $this->assertDatabaseHas('admin_actions', [
            'user_id' => $developer->id,
            'action' => 'optimize:clear',
        ]);

        $this->assertSame(1, AdminAction::count());
    }
}
