<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\BusinessIntelligenceDashboard;
use App\Livewire\Admin\CertificadosPorMes;
use App\Livewire\Admin\CursosPorSede;
use App\Livewire\Admin\DistribucionEtaria;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Evaluacion;
use App\Models\Feedback;
use App\Models\IntentoEvaluacion;
use App\Models\Modulo;
use App\Models\PlanificacionCurso;
use App\Models\ProgresoModulo;
use App\Models\Sede;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
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
            ->assertSee('Dashboard analítico')
            ->assertDontSee('Business Intelligence')
            ->assertSee('Año')
            ->assertSee('Sede')
            ->assertSee('Estamento')
            ->assertSee('Curso')
            ->assertSee('Ver reportes')
            ->assertSee('Cobertura anual')
            ->assertSee('Base activa')
            ->assertSee('Planificaciones')
            ->assertSee('Oferta vigente')
            ->assertSee('Resumen')
            ->assertSee('Progreso')
            ->assertSee('Calidad')
            ->assertSee('Segmentos');
    }

    public function test_developer_can_render_the_business_intelligence_dashboard(): void
    {
        $developer = $this->createDev();

        $this->actingAs($developer)
            ->get(route('admin.dashboard.index'))
            ->assertOk()
            ->assertSee('Dashboard analítico');
    }

    public function test_business_intelligence_dashboard_filters_operational_data(): void
    {
        $admin = $this->createAdmin();
        $sede = Sede::create(['nombre' => 'Sede Norte']);
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $user = User::factory()->create([
            'activo' => true,
            'sede_id' => $sede->id,
            'estamento_id' => $estamento->id,
            'fecha_nacimiento' => now()->subYears(31)->toDateString(),
        ]);
        $user->assignRole('Trabajador');
        $course = Curso::factory()->create(['titulo' => 'Curso de seguridad operacional']);
        $course->estamentos()->attach($estamento->id);
        $module = Modulo::factory()->create(['curso_id' => $course->id]);
        $evaluationModule = Modulo::factory()->evaluacion()->create(['curso_id' => $course->id]);
        $evaluation = Evaluacion::factory()->create(['modulo_id' => $evaluationModule->id]);

        PlanificacionCurso::create([
            'curso_id' => $course->id,
            'sede_id' => $sede->id,
            'fecha_inicio' => now()->startOfYear()->addMonth(),
            'fecha_fin' => now()->startOfYear()->addMonths(2),
        ]);

        ProgresoModulo::create([
            'user_id' => $user->id,
            'modulo_id' => $module->id,
            'completado' => true,
            'fecha_completado' => now(),
        ]);

        Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $course->id,
            'codigo_verificacion' => 'CERT-BI-001',
            'ruta_pdf' => 'certificados/bi.pdf',
            'fecha_emision' => now(),
        ]);

        Feedback::factory()->create([
            'user_id' => $user->id,
            'curso_id' => $course->id,
            'categoria' => 'contenido',
            'rating' => 5,
            'created_at' => now(),
        ]);

        IntentoEvaluacion::create([
            'user_id' => $user->id,
            'evaluacion_id' => $evaluation->id,
            'puntaje' => 8,
            'total_preguntas' => 10,
            'aprobado' => true,
            'created_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(BusinessIntelligenceDashboard::class)
            ->set('sedeId', (string) $sede->id)
            ->set('estamentoId', (string) $estamento->id)
            ->set('cursoId', (string) $course->id)
            ->call('setView', 'progress')
            ->assertSet('sedeId', (string) $sede->id)
            ->assertSet('activeView', 'progress')
            ->assertSee('Curso de seguridad operacional')
            ->assertSee('Sede Norte')
            ->assertSee('Operaciones')
            ->assertSee('Embudo')
            ->assertSee('Cursos críticos')
            ->assertSee('100%')
            ->assertSee('5')
            ->call('setView', 'segments')
            ->assertSee('Analítica individual')
            ->assertSee('Colaboradores con señales accionables')
            ->assertSee('Curso de seguridad operacional')
            ->assertSee('Al día')
            ->assertSee($user->name);
    }

    public function test_sede_filter_uses_collaborator_sede_not_course_planning_sede_for_coverage(): void
    {
        $admin = $this->createAdmin();
        $sedeNorte = Sede::create(['nombre' => 'Sede Norte']);
        $sedeSur = Sede::create(['nombre' => 'Sede Sur']);
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $worker = User::factory()->create([
            'activo' => true,
            'sede_id' => $sedeNorte->id,
            'estamento_id' => $estamento->id,
        ]);
        $worker->assignRole('Trabajador');
        $adminInSameSede = User::factory()->create([
            'activo' => true,
            'sede_id' => $sedeNorte->id,
            'estamento_id' => $estamento->id,
        ]);
        $adminInSameSede->assignRole('Administrador');
        $course = Curso::factory()->create(['titulo' => 'Curso transversal certificado']);
        $course->estamentos()->attach($estamento->id);

        PlanificacionCurso::create([
            'curso_id' => $course->id,
            'sede_id' => $sedeSur->id,
            'fecha_inicio' => now()->startOfYear()->addMonth(),
            'fecha_fin' => now()->startOfYear()->addMonths(2),
        ]);

        Certificado::create([
            'user_id' => $worker->id,
            'curso_id' => $course->id,
            'codigo_verificacion' => 'CERT-SEDE-FILTER',
            'ruta_pdf' => 'certificados/sede-filter.pdf',
            'fecha_emision' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(BusinessIntelligenceDashboard::class)
            ->set('sedeId', (string) $sedeNorte->id)
            ->assertViewHas('kpis', fn (array $kpis): bool => $kpis['active_users'] === 1
                && $kpis['certified_users'] === 1
                && $kpis['completion_rate'] === 100
                && $kpis['planned_sessions'] === 0)
            ->assertViewHas('charts', fn (array $charts): bool => $charts['sedeCoverage']['data']['labels'] === ['Sede Norte']
                && $charts['sedeCoverage']['data']['datasets'][0]['data'] === [100]);
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
