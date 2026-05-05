<?php

namespace Tests\Feature;

use App\Livewire\CalendarioUsuario;
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

class CalendarioAuditGapsTest extends TestCase
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
        $admin = User::factory()->create(['estamento_id' => $estamento->id]);
        $admin->assignRole('Administrador');

        return $admin;
    }

    private function crearColaborador(?Sede $sede = null): User
    {
        $estamento = Estamento::create(['nombre' => 'Colaborador']);

        return User::factory()->create([
            'estamento_id' => $estamento->id,
            'sede_id' => $sede?->id,
        ]);
    }

    /** @test */
    public function test_admin_cannot_resize_to_negative_duration_in_monthly_view(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-04-10',
            'fecha_fin' => '2026-04-15',
        ]);

        // Intentar mover el inicio después del fin (día 20 > día 15)
        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->set('mesActual', 4)
            ->call('ajustarBordePlanificacion', $plan->id, 'inicio', 20);

        $plan->refresh();
        // La lógica actual en CalendarioCapacitaciones:
        // $nuevaFechaInicio = $fechaObjetivo->copy()->min($plan->fecha_fin->copy());
        // Por lo tanto, debería limitarse a la fecha_fin.
        $this->assertTrue($plan->fecha_inicio->lte($plan->fecha_fin));
        $this->assertSame($plan->fecha_fin->toDateString(), $plan->fecha_inicio->toDateString());
    }

    /** @test */
    public function test_admin_cannot_resize_to_negative_duration_in_annual_view(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-01-05', // Semana 2
            'fecha_fin' => '2026-01-18',    // Semana 3
        ]);

        // Intentar mover el fin antes del inicio (Semana 1 < Semana 2)
        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioActual', 2026)
            ->call('cambiarVista', 'anual')
            ->set('modoPlaneacion', true)
            ->call('ajustarBordePlanificacionSemana', $plan->id, 'fin', 1);

        $plan->refresh();
        // La lógica actual: $semana = max($semana, $semActualIni);
        $this->assertSame('2026-01-05', $plan->fecha_inicio->toDateString());
        $this->assertSame('2026-01-11', $plan->fecha_fin->toDateString()); // Mínimo 1 semana (la misma del inicio)
    }

    /** @test */
    public function test_user_with_null_sede_sees_global_courses_only(): void
    {
        $user = $this->crearColaborador(null); // sede_id null
        $this->actingAs($user);

        $sede = Sede::create(['nombre' => 'Sede Privada']);
        $cursoGlobal = Curso::factory()->create(['titulo' => 'Curso Global']);
        $cursoSede = Curso::factory()->create(['titulo' => 'Curso Sede']);

        $user->estamento->cursos()->attach([$cursoGlobal->id, $cursoSede->id]);

        $hoy = now();
        // Plan global
        PlanificacionCurso::create([
            'curso_id' => $cursoGlobal->id,
            'sede_id' => null,
            'fecha_inicio' => $hoy->copy()->startOfMonth(),
            'fecha_fin' => $hoy->copy()->endOfMonth(),
        ]);

        // Plan de sede específica
        PlanificacionCurso::create([
            'curso_id' => $cursoSede->id,
            'sede_id' => $sede->id,
            'fecha_inicio' => $hoy->copy()->startOfMonth(),
            'fecha_fin' => $hoy->copy()->endOfMonth(),
        ]);

        $component = Livewire::test(CalendarioUsuario::class);
        $titulos = collect($component->get('cursosDelMes'))->pluck('titulo');

        $this->assertContains('Curso Global', $titulos);
        $this->assertNotContains('Curso Sede', $titulos);
    }

    /** @test */
    public function test_copy_year_replace_mode_cleans_target_year_completely(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);
        $curso = Curso::factory()->create();

        // Plan en 2026 (Origen)
        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2026-05-04',
            'fecha_fin' => '2026-05-10',
            'notas' => 'Plan Origen',
        ]);

        // Plan en 2027 (Destino que debe ser borrado)
        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => '2027-08-01',
            'fecha_fin' => '2027-08-07',
            'notas' => 'Plan Basura',
        ]);

        Livewire::test(CalendarioCapacitaciones::class)
            ->set('anioOrigen', 2026)
            ->set('anioDestino', 2027)
            ->call('copiarAnio', 'replace');

        $this->assertDatabaseMissing('planificaciones_cursos', ['notas' => 'Plan Basura']);
        $this->assertDatabaseHas('planificaciones_cursos', ['notas' => 'Plan Origen', 'fecha_inicio' => '2027-05-03']);
        $this->assertSame(1, PlanificacionCurso::whereYear('fecha_inicio', 2027)->count());
    }
}
