<?php

namespace Tests\Feature;

use App\Livewire\CalendarioUsuario;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\PlanificacionCurso;
use App\Models\Sede;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarioUsuarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function crearColaboradora(?Sede $sede = null): User
    {
        $estamento = Estamento::create(['nombre' => 'Colaborador '.fake()->unique()->word()]);

        return User::factory()->create([
            'estamento_id' => $estamento->id,
            'sede_id' => $sede?->id,
        ]);
    }

    private function crearCursoConPlanificacion(
        User $colaboradora,
        ?Sede $sede,
        string $inicio,
        string $fin
    ): array {
        $curso = Curso::factory()->create();
        $colaboradora->estamento->cursos()->attach($curso->id);

        $plan = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'sede_id' => $sede?->id,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
        ]);

        return [$curso, $plan];
    }

    public function test_componente_carga_para_colaborador(): void
    {
        $colaboradora = $this->crearColaboradora();
        $this->actingAs($colaboradora);

        Livewire::test(CalendarioUsuario::class)
            ->assertSet('mesActual', now()->month)
            ->assertSet('anioActual', now()->year);
    }

    public function test_navegacion_cambia_mes(): void
    {
        $colaboradora = $this->crearColaboradora();
        $this->actingAs($colaboradora);

        $hoy = now();
        $mesSiguiente = $hoy->month === 12 ? 1 : $hoy->month + 1;
        $anioSiguiente = $hoy->month === 12 ? $hoy->year + 1 : $hoy->year;

        Livewire::test(CalendarioUsuario::class)
            ->call('mesSiguiente')
            ->assertSet('mesActual', $mesSiguiente)
            ->assertSet('anioActual', $anioSiguiente);
    }

    public function test_ir_a_hoy_restablece_mes_actual(): void
    {
        $colaboradora = $this->crearColaboradora();
        $this->actingAs($colaboradora);

        $hoy = now();

        Livewire::test(CalendarioUsuario::class)
            ->set('mesActual', $hoy->month === 1 ? 12 : $hoy->month - 1)
            ->set('anioActual', $hoy->month === 1 ? $hoy->year - 1 : $hoy->year)
            ->call('irAHoy')
            ->assertSet('mesActual', $hoy->month)
            ->assertSet('anioActual', $hoy->year);
    }

    public function test_colaboradora_ve_curso_de_su_sede(): void
    {
        $sede = Sede::create(['nombre' => 'Hualpén']);
        $colaboradora = $this->crearColaboradora($sede);
        $this->actingAs($colaboradora);

        $hoy = now();
        [$curso] = $this->crearCursoConPlanificacion(
            $colaboradora,
            $sede,
            $hoy->copy()->startOfMonth()->toDateString(),
            $hoy->copy()->endOfMonth()->toDateString()
        );

        $component = Livewire::test(CalendarioUsuario::class);

        $cursosTitulos = collect($component->get('cursosDelMes'))->pluck('titulo')->all();
        $this->assertContains($curso->titulo, $cursosTitulos);
    }

    public function test_colaboradora_ve_curso_global_sin_sede(): void
    {
        $sede = Sede::create(['nombre' => 'Hualpén']);
        $colaboradora = $this->crearColaboradora($sede);
        $this->actingAs($colaboradora);

        $hoy = now();
        [$curso] = $this->crearCursoConPlanificacion(
            $colaboradora,
            null,
            $hoy->copy()->startOfMonth()->toDateString(),
            $hoy->copy()->endOfMonth()->toDateString()
        );

        $component = Livewire::test(CalendarioUsuario::class);

        $cursosTitulos = collect($component->get('cursosDelMes'))->pluck('titulo')->all();
        $this->assertContains($curso->titulo, $cursosTitulos);
    }

    public function test_colaboradora_no_ve_curso_de_otra_sede(): void
    {
        $sedeHualpen = Sede::create(['nombre' => 'Hualpén']);
        $sedeCoyhaique = Sede::create(['nombre' => 'Coyhaique']);
        $colaboradora = $this->crearColaboradora($sedeHualpen);
        $this->actingAs($colaboradora);

        $hoy = now();
        [$curso] = $this->crearCursoConPlanificacion(
            $colaboradora,
            $sedeCoyhaique,
            $hoy->copy()->startOfMonth()->toDateString(),
            $hoy->copy()->endOfMonth()->toDateString()
        );

        $component = Livewire::test(CalendarioUsuario::class);

        $cursosTitulos = collect($component->get('cursosDelMes'))->pluck('titulo')->all();
        $this->assertNotContains($curso->titulo, $cursosTitulos);
    }

    public function test_cursos_proximos_aparecen_en_seccion_proximos(): void
    {
        $sede = Sede::create(['nombre' => 'Hualpén']);
        $colaboradora = $this->crearColaboradora($sede);
        $this->actingAs($colaboradora);

        $hoy = now();
        $inicioProximo = $hoy->copy()->endOfMonth()->addDay()->toDateString();
        $finProximo = $hoy->copy()->endOfMonth()->addDays(15)->toDateString();

        [$curso] = $this->crearCursoConPlanificacion(
            $colaboradora,
            $sede,
            $inicioProximo,
            $finProximo
        );

        $component = Livewire::test(CalendarioUsuario::class);

        $proximosTitulos = collect($component->get('proximosCursos'))->pluck('titulo')->all();
        $this->assertContains($curso->titulo, $proximosTitulos);
    }

    public function test_preview_muestra_curso_creado_por_usuario_en_calendario(): void
    {
        $sede = Sede::create(['nombre' => 'Hualpén']);
        $otraSede = Sede::create(['nombre' => 'Coyhaique']);
        $capacitador = $this->crearColaboradora($sede);
        $capacitador->assignRole('Capacitador Interno');
        $this->actingAs($capacitador);
        $this->withSession(['preview_mode' => true]);

        $hoy = now();
        $curso = Curso::factory()->create(['capacitador_id' => $capacitador->id]);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'sede_id' => $otraSede->id,
            'fecha_inicio' => $hoy->copy()->startOfMonth()->toDateString(),
            'fecha_fin' => $hoy->copy()->endOfMonth()->toDateString(),
        ]);

        $component = Livewire::test(CalendarioUsuario::class);

        $cursosTitulos = collect($component->get('cursosDelMes'))->pluck('titulo')->all();
        $this->assertContains($curso->titulo, $cursosTitulos);
    }

    public function test_admin_preview_muestra_todos_los_cursos_en_calendario(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $this->actingAs($admin);
        $this->withSession(['preview_mode' => true]);

        $hoy = now();
        $curso = Curso::factory()->create();

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => $hoy->copy()->startOfMonth()->toDateString(),
            'fecha_fin' => $hoy->copy()->endOfMonth()->toDateString(),
        ]);

        $component = Livewire::test(CalendarioUsuario::class);

        $cursosTitulos = collect($component->get('cursosDelMes'))->pluck('titulo')->all();
        $this->assertContains($curso->titulo, $cursosTitulos);
    }

    public function test_grid_mensual_tiene_dias_correctos(): void
    {
        $colaboradora = $this->crearColaboradora();
        $this->actingAs($colaboradora);

        $component = Livewire::test(CalendarioUsuario::class)
            ->set('mesActual', 5)
            ->set('anioActual', 2026);

        $semanas = $component->get('semanasDelMes');
        $this->assertNotEmpty($semanas);

        // Mayo 2026 tiene 31 días
        $diasDelMes = collect($semanas)
            ->flatMap(fn ($s) => $s['dias'])
            ->where('esMesActual', true)
            ->count();
        $this->assertSame(31, $diasDelMes);
    }
}
