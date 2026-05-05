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
            ->set('modoPlaneacion', true)
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
            ->set('modoPlaneacion', true)
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

    public function test_vista_anual_muestra_control_ir_a_mes(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('cambiarVista', 'anual')
            ->assertSee('Ir a mes')
            ->assertSee('Copiar año');
    }

    public function test_vista_mensual_no_muestra_control_ir_a_mes(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->call('cambiarVista', 'mensual')
            ->assertDontSee('Ir a mes');
    }

    public function test_vista_anual_oculta_controles_de_edicion_si_el_modo_no_esta_activo(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
        ]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->assertDontSee('Cursos disponibles')
            ->assertDontSee('abrirModalBorrado', false);
    }

    public function test_vista_anual_muestra_controles_de_edicion_con_el_modo_activo(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->assertSee('Cursos disponibles')
            ->assertSee('abrirModalBorrado', false)
            ->assertSee('cursor-grab', false);
    }

    public function test_admin_can_open_existing_annual_block_for_editing_when_edit_mode_is_active(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $sede = Sede::create(['nombre' => 'Santiago']);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'sede_id' => $sede->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-18',
            'notas' => 'Bloque editable',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('editarPlanificacion', $plan->id)
            ->assertSet('mostrarModalPlanificacion', true)
            ->assertSet('editandoId', $plan->id)
            ->assertSet('cursoId', $curso->id)
            ->assertSet('sedeIdPlan', $sede->id)
            ->assertSet('fechaInicioPlan', '2026-01-05')
            ->assertSet('fechaFinPlan', '2026-01-18')
            ->assertSet('notas', 'Bloque editable');
    }

    public function test_admin_cannot_edit_existing_annual_block_without_edit_mode(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->call('editarPlanificacion', $plan->id)
            ->assertForbidden();
    }

    public function test_annual_block_uses_separate_click_and_move_controls(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->assertSee('cursor-pointer', false)
            ->assertSee('cursor-grab', false)
            ->assertSee('startMove', false)
            ->assertSee('wire:click="editarPlanificacion', false)
            ->assertSee('@click.stop', false);
    }

    public function test_annual_grid_uses_click_handlers_without_self_modifier(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->assertSee('handleCellMouseDown', false)
            ->assertSee('handleCellClick', false)
            ->assertDontSee('@click.self', false)
            ->assertDontSee('@mousedown.self', false);
    }

    public function test_annual_grid_uses_wider_slots_and_calendar_scroller_ref(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->assertSee('var(--planning-week-width)', false)
            ->assertSee('x-ref="calendarScroller"', false)
            ->assertSee('data-week-header', false);
    }

    public function test_copy_year_button_state_renders_modal_content(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->call('abrirModalCopiarAnio')
            ->assertSet('mostrarModalCopiarAnio', true)
            ->assertSee('Copiar planificación anual');
    }

    public function test_admin_can_prefill_annual_planification_from_sidebar_drop_range(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $sede = Sede::create(['nombre' => 'Concepción']);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('abrirModalAnualConCursoRango', $curso->id, 2, 4, $sede->id)
            ->assertSet('cursoId', $curso->id)
            ->assertSet('sedeIdPlan', $sede->id)
            ->assertSet('semanaInicioPlan', 2)
            ->assertSet('semanaFinPlan', 4)
            ->assertSet('fechaInicioPlan', '2026-01-05')
            ->assertSet('fechaFinPlan', '2026-01-25')
            ->assertSet('mostrarModalPlanificacion', true);
    }

    public function test_admin_can_create_single_week_annual_planification_from_sidebar_drop(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $sede = Sede::create(['nombre' => 'Concepción']);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('guardarPlanificacionRapidaAnualDesdeSidebar', $curso->id, 4, $sede->id);

        $this->assertDatabaseHas('planificaciones_cursos', [
            'curso_id' => $curso->id,
            'sede_id' => $sede->id,
            'fecha_inicio' => '2026-01-19',
            'fecha_fin' => '2026-01-25',
        ]);
    }

    public function test_annual_drag_and_action_controls_use_livewire_responsive_hooks(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Curso::factory()->create(['capacitador_id' => $admin->id]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('precalentarCalendario', 'siguiente')
            ->assertSee('@drop.prevent="dropCourseOnCell', false)
            ->assertSee('@dragover.prevent="enterCourseCell', false)
            ->assertSee("preheat('siguiente')", false)
            ->assertSee('planning-action', false)
            ->assertSee('data-loading', false)
            ->assertSee('motion-reduce:transition-none', false);
    }

    public function test_admin_can_move_planificacion_between_weeks_and_sedes(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $sede = Sede::create(['nombre' => 'Concepción']);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-18',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('moverPlanificacionSemanas', $plan->id, 4, $sede->id);

        $plan->refresh();

        $this->assertSame($sede->id, $plan->sede_id);
        $this->assertSame('2026-01-19', $plan->fecha_inicio->toDateString());
        $this->assertSame('2026-02-01', $plan->fecha_fin->toDateString());
    }

    public function test_admin_can_resize_planificacion_by_week(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-18',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('ajustarBordePlanificacionSemana', $plan->id, 'fin', 4);

        $plan->refresh();

        $this->assertSame('2026-01-25', $plan->fecha_fin->toDateString());
    }

    public function test_non_admin_cannot_move_planificacion(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Trabajador');
        $this->actingAs($user);

        $curso = Curso::factory()->create();
        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->call('moverPlanificacionSemanas', $plan->id, 4)
            ->assertForbidden();
    }

    public function test_copy_year_with_existing_destination_requires_a_choice(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
        ]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2027-02-01',
            'fecha_fin' => '2027-02-07',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('anioOrigen', 2026)
            ->set('anioDestino', 2027)
            ->call('copiarAnio')
            ->assertSet('anioDestinoTienePlanificaciones', true);

        $this->assertSame(1, PlanificacionCurso::whereYear('fecha_inicio', 2027)->count());
    }

    public function test_copy_year_can_append_to_existing_destination(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
            'notas' => 'Origen',
        ]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2027-02-01',
            'fecha_fin' => '2027-02-07',
            'notas' => 'Existente',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('anioOrigen', 2026)
            ->set('anioDestino', 2027)
            ->call('copiarAnio', 'append');

        $this->assertSame(2, PlanificacionCurso::whereYear('fecha_inicio', 2027)->count());
        $this->assertDatabaseHas('planificaciones_cursos', [
            'fecha_inicio' => '2027-01-04',
            'fecha_fin' => '2027-01-10',
            'notas' => 'Origen',
        ]);
    }

    public function test_copy_year_can_replace_existing_destination(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05',
            'fecha_fin' => '2026-01-11',
            'notas' => 'Origen',
        ]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2027-02-01',
            'fecha_fin' => '2027-02-07',
            'notas' => 'Debe borrarse',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('anioOrigen', 2026)
            ->set('anioDestino', 2027)
            ->call('copiarAnio', 'replace');

        $this->assertSame(1, PlanificacionCurso::whereYear('fecha_inicio', 2027)->count());
        $this->assertDatabaseMissing('planificaciones_cursos', [
            'notas' => 'Debe borrarse',
        ]);
        $this->assertDatabaseHas('planificaciones_cursos', [
            'fecha_inicio' => '2027-01-04',
            'fecha_fin' => '2027-01-10',
            'notas' => 'Origen',
        ]);
    }

    public function test_copy_year_allows_changing_source_year(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2025-03-03',
            'fecha_fin' => '2025-03-16',
            'notas' => 'Origen 2025',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('anioOrigen', 2025)
            ->set('anioDestino', 2028)
            ->call('copiarAnio');

        $this->assertDatabaseHas('planificaciones_cursos', [
            'fecha_inicio' => '2028-02-28',
            'fecha_fin' => '2028-03-12',
            'notas' => 'Origen 2025',
        ]);
    }

    public function test_copy_year_modal_detects_non_empty_destination_when_target_changes(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2027-02-01',
            'fecha_fin' => '2027-02-07',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('abrirModalCopiarAnio')
            ->assertSet('anioOrigen', 2026)
            ->assertSet('anioDestino', 2027)
            ->assertSet('anioDestinoTienePlanificaciones', true)
            ->set('anioDestino', 2028)
            ->assertSet('anioDestinoTienePlanificaciones', false);
    }

    public function test_guardar_planificacion_rapida_anual_rechaza_curso_inexistente(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('guardarPlanificacionRapidaAnual', 99999, 1, null)
            ->assertHasErrors(['cursoId']);

        $this->assertDatabaseCount('planificaciones_cursos', 0);
    }

    public function test_guardar_planificacion_rapida_anual_desde_sidebar_rechaza_sede_inexistente(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);

        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('guardarPlanificacionRapidaAnualDesdeSidebar', $curso->id, 4, 99999)
            ->assertHasErrors(['sedeIdPlan']);

        $this->assertDatabaseCount('planificaciones_cursos', 0);
    }
}
