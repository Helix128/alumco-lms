<?php

namespace Tests\Feature;

use App\Livewire\Capacitador\CalendarioCapacitaciones;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\PlanificacionCurso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarioCapacitacionesTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): User
    {
        $estamento = Estamento::create(['nombre' => 'Administrador']);

        return User::factory()->create([
            'estamento_id' => $estamento->id,
        ]);
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
            'curso_id'     => $curso->id,
            'fecha_inicio' => '2026-04-05',
            'fecha_fin'    => '2026-04-09', // duración = 4 días
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
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
}
