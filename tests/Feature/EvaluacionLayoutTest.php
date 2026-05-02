<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class EvaluacionLayoutTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_worker_evaluation_uses_responsive_layout_without_hidden_previous_button_space(): void
    {
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $trabajador = $this->createTrabajador();
        $trabajador->update(['estamento_id' => $estamento->id]);

        $curso = Curso::factory()->create();
        $estamento->cursos()->attach($curso);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(),
        ]);

        $modulo = Modulo::factory()->create([
            'curso_id' => $curso->id,
            'orden' => 1,
            'tipo_contenido' => 'evaluacion',
            'titulo' => 'Evaluacion de seguridad',
        ]);
        $evaluacion = Evaluacion::create(['modulo_id' => $modulo->id]);
        $pregunta = Pregunta::create([
            'evaluacion_id' => $evaluacion->id,
            'enunciado' => 'Cual es la conducta correcta?',
            'orden' => 1,
        ]);

        Opcion::create([
            'pregunta_id' => $pregunta->id,
            'texto' => 'Usar el equipo de proteccion personal.',
            'es_correcta' => true,
            'orden' => 1,
        ]);
        Opcion::create([
            'pregunta_id' => $pregunta->id,
            'texto' => 'Omitir la revision inicial.',
            'es_correcta' => false,
            'orden' => 2,
        ]);

        $this->actingAs($trabajador)
            ->get(route('modulos.show', [$curso, $modulo]))
            ->assertOk()
            ->assertSee('max-w-[90rem]', false)
            ->assertSee('lg:grid-cols-[minmax(0,1fr)_20rem]', false)
            ->assertSee('sm:col-span-3', false)
            ->assertDontSee('Anterior');
    }
}
