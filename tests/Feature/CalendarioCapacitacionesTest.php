<?php

namespace Tests\Feature;

use App\Livewire\Capacitador\CalendarioCapacitaciones;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\PlanificacionCurso;
use App\Models\Sede;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarioCapacitacionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function crearAdmin(): User
    {
        $estamento = Estamento::create(['nombre' => 'Administrador']);

        $admin = User::factory()->create([
            'estamento_id' => $estamento->id,
        ]);

        $admin->assignRole('Administrador');

        return $admin;
    }

    public function test_admin_can_resize_a_planificacion_from_gantt_handles(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create([
            'capacitador_id' => $admin->id,
        ]);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-04-10',
            'fecha_fin' => '2026-04-15',
            'notas' => 'Periodo inicial',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('mesActual', 4)
            ->call('cambiarVista', 'mensual')
            ->call('ajustarBordePlanificacion', $plan->id, 'inicio', 8)
            ->call('ajustarBordePlanificacion', $plan->id, 'fin', 18);

        $plan->refresh();

        $this->assertSame('2026-04-08', $plan->fecha_inicio->toDateString());
        $this->assertSame('2026-04-18', $plan->fecha_fin->toDateString());
    }

    public function test_calendar_bars_span_the_full_selected_range_inside_the_week(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create([
            'capacitador_id' => $admin->id,
        ]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-04-08',
            'fecha_fin' => '2026-04-10',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('mesActual', 4)
            ->call('cambiarVista', 'mensual')
            ->assertSee('grid-column: 3 / span 3; grid-row: 2', false);
    }

    public function test_gantt_background_cells_stay_on_the_same_course_row(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create([
            'capacitador_id' => $admin->id,
        ]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-04-10',
            'fecha_fin' => '2026-04-15',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('mesActual', 4)
            ->call('cambiarVista', 'mensual')
            ->assertSee('grid-column: 5 / span 3; grid-row: 2', false)
            ->assertSee('grid-column: 1 / span 3; grid-row: 2', false);
    }

    public function test_mover_planificacion_preserves_duration(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-04-05',
            'fecha_fin' => '2026-04-09', // duración = 4 días
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('mesActual', 4)
            ->call('cambiarVista', 'mensual')
            ->call('moverPlanificacion', $plan->id, 10); // mover a día 10

        $plan->refresh();
        $this->assertEquals('2026-04-10', $plan->fecha_inicio->toDateString());
        $this->assertEquals('2026-04-14', $plan->fecha_fin->toDateString()); // duración intacta
    }

    public function test_ir_a_hoy_resets_to_current_month(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $hoy = now();

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('mesActual', $hoy->month === 1 ? 12 : $hoy->month - 1)
            ->set('anioActual', $hoy->month === 1 ? $hoy->year - 1 : $hoy->year)
            ->call('irAHoy')
            ->assertSet('mesActual', $hoy->month)
            ->assertSet('anioActual', $hoy->year);
    }

    public function test_abrir_modal_con_curso_prefills_curso_id(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('abrirModalConCurso', $curso->id)
            ->assertSet('cursoId', $curso->id)
            ->assertSet('mostrarModalPlanificacion', true);
    }

    public function test_default_view_is_mensual(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->assertSet('modoVista', 'mensual');
    }

    public function test_abrir_modal_anual_mes_prefills_month_range(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->call('abrirModalAnualMes', 3)
            ->assertSet('fechaInicioPlan', '2026-03-01')
            ->assertSet('fechaFinPlan', '2026-03-31')
            ->assertSet('mostrarModalPlanificacion', true);
    }

    public function test_abrir_modal_anual_mes_sets_sede(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $sede = Sede::create(['nombre' => 'Santiago Test']);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->call('abrirModalAnualMes', 6, $sede->id)
            ->assertSet('sedeIdPlan', $sede->id)
            ->assertSet('fechaInicioPlan', '2026-06-01');
    }

    public function test_abrir_quick_add_sets_fecha_and_shows_popover(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('abrirQuickAdd', '2026-05-15')
            ->assertSet('mostrarQuickAdd', true)
            ->assertSet('quickAddFecha', '2026-05-15')
            ->assertSet('cursoId', null);
    }

    public function test_guardar_quick_add_creates_single_day_planificacion(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('abrirQuickAdd', '2026-05-15')
            ->call('seleccionarCurso', $curso->id)
            ->call('guardarQuickAdd')
            ->assertSet('mostrarQuickAdd', false);

        $this->assertDatabaseHas('planificaciones_cursos', [
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-05-15',
            'fecha_fin' => '2026-05-15',
        ]);
    }

    public function test_guardar_quick_add_requires_curso(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('abrirQuickAdd', '2026-05-15')
            ->call('guardarQuickAdd')
            ->assertHasErrors(['cursoId']);
    }

    public function test_escalar_quick_add_abre_modal_completo(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('abrirQuickAdd', '2026-05-15')
            ->call('seleccionarCurso', $curso->id)
            ->call('escalarQuickAddAModal')
            ->assertSet('mostrarQuickAdd', false)
            ->assertSet('mostrarModalPlanificacion', true)
            ->assertSet('fechaInicioPlan', '2026-05-15')
            ->assertSet('fechaFinPlan', '2026-05-15')
            ->assertSet('cursoId', $curso->id);
    }

    public function test_guardar_planificacion_en_vista_anual_usa_fechas_directas(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('cambiarVista', 'anual')
            ->set('cursoId', $curso->id)
            ->set('fechaInicioPlan', '2026-04-01')
            ->set('fechaFinPlan', '2026-04-30')
            ->call('guardarPlanificacion');

        $this->assertDatabaseHas('planificaciones_cursos', [
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-04-30',
        ]);
    }

    public function test_saltar_a_mes_posiciona_ventana_en_primera_semana(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        // January 2026 starts in week 1
        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->call('saltarAMes', 1)
            ->assertSet('ventanaInicioSemana', 1);
    }

    public function test_vista_anual_muestra_control_ir_a_mes(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('cambiarVista', 'anual')
            ->assertSee('Ir a mes')
            ->assertSee('Mostrando');
    }

    public function test_vista_mensual_no_muestra_control_ir_a_mes(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('cambiarVista', 'mensual')
            ->assertDontSee('Ir a mes');
    }

    public function test_ventana_siguiente_avanza_16_semanas(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('ventanaInicioSemana', 1)
            ->call('ventanaSiguiente')
            ->assertSet('ventanaInicioSemana', 17);
    }

    public function test_ventana_anterior_no_va_debajo_de_uno(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('ventanaInicioSemana', 5)
            ->call('ventanaAnterior')
            ->assertSet('ventanaInicioSemana', 1);
    }
}
