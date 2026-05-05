<?php

namespace Tests\Feature;

use App\Livewire\Capacitador\EditarEvaluacion;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Evaluacion;
use App\Models\GlobalSetting;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\Pregunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class EditarEvaluacionTest extends TestCase
{
    use RefreshDatabase;

    private User $capacitador;

    private Curso $curso;

    private Evaluacion $evaluacion;

    protected function setUp(): void
    {
        parent::setUp();

        $estamento = Estamento::create(['nombre' => 'Capacitador Interno']);
        $this->capacitador = User::factory()->create(['estamento_id' => $estamento->id]);

        $this->curso = Curso::factory()->create(['capacitador_id' => $this->capacitador->id]);
        $modulo = Modulo::factory()->evaluacion()->create(['curso_id' => $this->curso->id]);
        $this->evaluacion = Evaluacion::factory()->create(['modulo_id' => $modulo->id]);

        GlobalSetting::set('evaluacion_puntos_aprobacion', 70);
    }

    private function mountComponent(): Testable
    {
        return Livewire::actingAs($this->capacitador)
            ->test(EditarEvaluacion::class, [
                'evaluacion' => $this->evaluacion,
                'curso' => $this->curso,
            ]);
    }

    public function test_agregar_opcion_orden_uno_si_primera(): void
    {
        $pregunta = Pregunta::factory()->create(['evaluacion_id' => $this->evaluacion->id, 'orden' => 1]);

        $this->mountComponent()->call('agregarOpcion', $pregunta->id);

        $this->assertDatabaseHas('opciones', [
            'pregunta_id' => $pregunta->id,
            'orden' => 1,
        ]);
    }

    public function test_agregar_opcion_asigna_orden_correcto(): void
    {
        $pregunta = Pregunta::factory()->create(['evaluacion_id' => $this->evaluacion->id, 'orden' => 1]);
        Opcion::factory()->create(['pregunta_id' => $pregunta->id, 'orden' => 1]);

        $this->mountComponent()->call('agregarOpcion', $pregunta->id);

        $this->assertDatabaseHas('opciones', [
            'pregunta_id' => $pregunta->id,
            'orden' => 2,
        ]);
    }

    public function test_guardar_enunciado_persiste_en_db(): void
    {
        $pregunta = Pregunta::factory()->create([
            'evaluacion_id' => $this->evaluacion->id,
            'enunciado' => 'Pregunta original',
            'orden' => 1,
        ]);

        $this->mountComponent()
            ->set('preguntas.0.enunciado', 'Enunciado actualizado')
            ->call('guardarEnunciado', $pregunta->id);

        $this->assertDatabaseHas('preguntas', [
            'id' => $pregunta->id,
            'enunciado' => 'Enunciado actualizado',
        ]);
    }

    public function test_guardar_texto_opcion_persiste_en_db(): void
    {
        $pregunta = Pregunta::factory()->create(['evaluacion_id' => $this->evaluacion->id, 'orden' => 1]);
        $opcion = Opcion::factory()->create(['pregunta_id' => $pregunta->id, 'texto' => '', 'orden' => 1]);

        $this->mountComponent()
            ->set('preguntas.0.opciones.0.texto', 'Texto de la opción')
            ->call('guardarTextoOpcion', $opcion->id);

        $this->assertDatabaseHas('opciones', [
            'id' => $opcion->id,
            'texto' => 'Texto de la opción',
        ]);
    }

    public function test_reordenar_preguntas_actualiza_orden_db(): void
    {
        $p1 = Pregunta::factory()->create(['evaluacion_id' => $this->evaluacion->id, 'orden' => 1]);
        $p2 = Pregunta::factory()->create(['evaluacion_id' => $this->evaluacion->id, 'orden' => 2]);

        $this->mountComponent()->call('reordenarPreguntas', [$p2->id, $p1->id]);

        $this->assertDatabaseHas('preguntas', ['id' => $p2->id, 'orden' => 1]);
        $this->assertDatabaseHas('preguntas', ['id' => $p1->id, 'orden' => 2]);
    }

    public function test_reordenar_preguntas_ignora_ids_de_otras_evaluaciones(): void
    {
        $otraEvaluacion = Evaluacion::factory()->create();
        $preguntaAjena = Pregunta::factory()->create(['evaluacion_id' => $otraEvaluacion->id, 'orden' => 1]);
        $preguntaPropia = Pregunta::factory()->create(['evaluacion_id' => $this->evaluacion->id, 'orden' => 1]);

        $this->mountComponent()->call('reordenarPreguntas', [$preguntaAjena->id, $preguntaPropia->id]);

        $this->assertDatabaseHas('preguntas', ['id' => $preguntaAjena->id, 'orden' => 1]);
    }

    public function test_flash_dispatch_al_guardar_enunciado(): void
    {
        $pregunta = Pregunta::factory()->create([
            'evaluacion_id' => $this->evaluacion->id,
            'enunciado' => 'Pregunta original',
            'orden' => 1,
        ]);

        $this->mountComponent()
            ->set('preguntas.0.enunciado', 'Texto válido con longitud')
            ->call('guardarEnunciado', $pregunta->id)
            ->assertDispatched('flash-guardado');
    }

    public function test_resumen_calcula_puntos_aprobacion(): void
    {
        Pregunta::factory()->count(10)->create(['evaluacion_id' => $this->evaluacion->id]);

        $component = $this->mountComponent();

        $resumen = $component->instance()->resumen;

        $this->assertEquals(10, $resumen['total']);
        $this->assertEquals(7, $resumen['puntosNecesarios']);
        $this->assertEquals(70, $resumen['porcentaje']);
    }

    public function test_resumen_cuenta_preguntas_sin_correcta(): void
    {
        $pregunta = Pregunta::factory()->create(['evaluacion_id' => $this->evaluacion->id, 'orden' => 1]);
        Opcion::factory()->create(['pregunta_id' => $pregunta->id, 'es_correcta' => false]);

        $resumen = $this->mountComponent()->instance()->resumen;

        $this->assertEquals(1, $resumen['preguntasSinCorrecta']);
        $this->assertEquals(0, $resumen['preguntasSinOpciones']);
    }

    public function test_resumen_cuenta_preguntas_sin_opciones(): void
    {
        Pregunta::factory()->create(['evaluacion_id' => $this->evaluacion->id, 'orden' => 1]);

        $resumen = $this->mountComponent()->instance()->resumen;

        $this->assertEquals(1, $resumen['preguntasSinOpciones']);
        $this->assertEquals(0, $resumen['preguntasSinCorrecta']);
    }

    public function test_eliminar_pregunta_no_puede_borrar_pregunta_ajena(): void
    {
        $otraEvaluacion = Evaluacion::factory()->create();
        $preguntaAjena = Pregunta::factory()->create(['evaluacion_id' => $otraEvaluacion->id, 'orden' => 1]);

        $this->mountComponent()->call('eliminarPregunta', $preguntaAjena->id);

        $this->assertDatabaseHas('preguntas', ['id' => $preguntaAjena->id]);
    }

    public function test_eliminar_opcion_no_puede_borrar_opcion_ajena(): void
    {
        $otraEvaluacion = Evaluacion::factory()->create();
        $preguntaAjena = Pregunta::factory()->create(['evaluacion_id' => $otraEvaluacion->id, 'orden' => 1]);
        $opcionAjena = Opcion::factory()->create(['pregunta_id' => $preguntaAjena->id]);

        $this->mountComponent()->call('eliminarOpcion', $opcionAjena->id);

        $this->assertDatabaseHas('opciones', ['id' => $opcionAjena->id]);
    }

    public function test_agregar_opcion_rechaza_pregunta_ajena(): void
    {
        $otraEvaluacion = Evaluacion::factory()->create();
        $preguntaAjena = Pregunta::factory()->create(['evaluacion_id' => $otraEvaluacion->id, 'orden' => 1]);

        $this->mountComponent()
            ->call('agregarOpcion', $preguntaAjena->id)
            ->assertForbidden();
    }
}
