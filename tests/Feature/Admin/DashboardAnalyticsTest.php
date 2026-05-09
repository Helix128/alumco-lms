<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\Admin\CertificadosPorMes;
use App\Livewire\Admin\CursosPorSede;
use App\Livewire\Admin\DistribucionEtaria;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\PlanificacionCurso;
use App\Models\Sede;
use App\Models\User;
use App\Services\Analytics\LearningAnalyticsService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class DashboardAnalyticsTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Cache::flush();
    }

    public function test_admin_dashboard_renders_the_new_analytics_layout(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard.index'))
            ->assertOk()
            ->assertSee('Dashboard analítico de la operación')
            ->assertSee('Ver reportes')
            ->assertSee('Usuarios activos')
            ->assertSee('Cursos activos')
            ->assertSee('Certificados')
            ->assertSee('Cumplimiento')
            ->assertSee('Participantes asignados')
            ->assertSee('Iniciaron')
            ->assertSee('Completaron')
            ->assertSee('En riesgo')
            ->assertSee('Feedback promedio');
    }

    public function test_admin_dashboard_controller_provides_lms_stats_payload(): void
    {
        $controller = app(DashboardController::class);
        $view = $controller->index();

        $viewData = $view->getData();

        $this->assertArrayHasKey('stats', $viewData);
        $this->assertArrayHasKey('lmsStats', $viewData);
        $this->assertArrayHasKey('total_participantes', $viewData['lmsStats']);
        $this->assertArrayHasKey('iniciados', $viewData['lmsStats']);
        $this->assertArrayHasKey('completados', $viewData['lmsStats']);
        $this->assertArrayHasKey('en_riesgo', $viewData['lmsStats']);
        $this->assertArrayHasKey('feedback_promedio', $viewData['lmsStats']);
    }

    public function test_admin_dashboard_uses_aggregate_lms_summary(): void
    {
        $admin = $this->createAdmin();
        $analyticsService = Mockery::mock(LearningAnalyticsService::class);
        $analyticsService->shouldReceive('summaryFromAggregates')
            ->once()
            ->andReturn([
                'total_participantes' => 0,
                'iniciados' => 0,
                'completados' => 0,
                'en_riesgo' => 0,
                'feedback_promedio' => null,
            ]);
        $analyticsService->shouldReceive('summaryForCourses')->never();
        $this->app->instance(LearningAnalyticsService::class, $analyticsService);

        $this->actingAs($admin)
            ->get(route('admin.dashboard.index'))
            ->assertOk();
    }

    public function test_monthly_certificate_chart_renders_current_year_series(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $course = Curso::factory()->create();

        Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $course->id,
            'codigo_verificacion' => 'CERT-ENE',
            'ruta_pdf' => 'certificados/test-ene.pdf',
            'fecha_emision' => now()->startOfYear()->addMonths(1),
        ]);

        Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $course->id,
            'codigo_verificacion' => 'CERT-JUL',
            'ruta_pdf' => 'certificados/test-jul.pdf',
            'fecha_emision' => now()->startOfYear()->addMonths(6),
        ]);

        Livewire::actingAs($admin)
            ->test(CertificadosPorMes::class)
            ->assertSet('currentYear', now()->year)
            ->assertSet('totalCertificados', 2)
            ->assertSet('mesPico', 'Feb')
            ->assertSet('certificadosMesPico', 1);
    }

    public function test_courses_by_sede_chart_renders_grouped_data(): void
    {
        $admin = $this->createAdmin();
        $sedeNorte = Sede::create(['nombre' => 'Sede Norte']);
        $sedeSur = Sede::create(['nombre' => 'Sede Sur']);
        $courseOne = Curso::factory()->create();
        $courseTwo = Curso::factory()->create();
        $courseThree = Curso::factory()->create();

        PlanificacionCurso::create([
            'curso_id' => $courseOne->id,
            'sede_id' => $sedeNorte->id,
            'fecha_inicio' => now()->startOfYear()->addMonth(),
            'fecha_fin' => now()->startOfYear()->addMonths(2),
        ]);

        PlanificacionCurso::create([
            'curso_id' => $courseTwo->id,
            'sede_id' => $sedeNorte->id,
            'fecha_inicio' => now()->startOfYear()->addMonths(2),
            'fecha_fin' => now()->startOfYear()->addMonths(3),
        ]);

        PlanificacionCurso::create([
            'curso_id' => $courseThree->id,
            'sede_id' => $sedeSur->id,
            'fecha_inicio' => now()->startOfYear()->addMonths(4),
            'fecha_fin' => now()->startOfYear()->addMonths(5),
        ]);

        Livewire::actingAs($admin)
            ->test(CursosPorSede::class)
            ->assertSet('currentYear', now()->year)
            ->assertSet('totalCursos', 3)
            ->assertSet('totalPlanificaciones', 3)
            ->assertSet('sedeLider', 'Sede Norte')
            ->assertSet('sedeLiderCantidad', 2);
    }

    public function test_age_distribution_chart_groups_active_users_by_range(): void
    {
        $admin = User::factory()->create([
            'fecha_nacimiento' => now()->subYears(40)->toDateString(),
            'activo' => true,
        ]);
        $admin->assignRole('Administrador');
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $sede = Sede::create(['nombre' => 'Casa Matriz']);

        User::factory()->create([
            'estamento_id' => $estamento->id,
            'sede_id' => $sede->id,
            'fecha_nacimiento' => now()->subYears(22)->toDateString(),
            'activo' => true,
        ])->assignRole('Trabajador');

        User::factory()->create([
            'estamento_id' => $estamento->id,
            'sede_id' => $sede->id,
            'fecha_nacimiento' => now()->subYears(31)->toDateString(),
            'activo' => true,
        ])->assignRole('Trabajador');

        Livewire::actingAs($admin)
            ->test(DistribucionEtaria::class)
            ->assertSet('totalUsuarios', 3)
            ->assertSet('rangoDominante', '18-25')
            ->assertSet('rangoDominanteCantidad', 1);
    }
}
