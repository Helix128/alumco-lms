<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Modulo;
use App\Models\PlanificacionCurso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModuloContentSanitizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');
    }

    public function test_store_strips_xss_from_contenido(): void
    {
        $estamento = Estamento::create(['nombre' => 'Capacitador Interno']);
        $capacitador = User::factory()->create(['estamento_id' => $estamento->id]);
        $capacitador->assignRole('Capacitador Interno');

        $curso = Curso::factory()->create(['capacitador_id' => $capacitador->id]);

        $this->actingAs($capacitador)
            ->post(route('capacitador.cursos.modulos.store', $curso), [
                'titulo' => 'Módulo de prueba',
                'tipo_contenido' => 'texto',
                'contenido' => '<p>Contenido válido</p><script>alert("xss")</script>',
            ]);

        $modulo = Modulo::where('curso_id', $curso->id)->first();
        $this->assertNotNull($modulo);
        $this->assertStringNotContainsString('<script>', $modulo->contenido);
        $this->assertStringContainsString('Contenido válido', $modulo->contenido);
    }

    public function test_update_strips_xss_from_contenido(): void
    {
        $estamento = Estamento::create(['nombre' => 'Capacitador Interno']);
        $capacitador = User::factory()->create(['estamento_id' => $estamento->id]);
        $capacitador->assignRole('Capacitador Interno');

        $curso = Curso::factory()->create(['capacitador_id' => $capacitador->id]);
        $modulo = Modulo::factory()->texto()->create(['curso_id' => $curso->id]);

        $this->actingAs($capacitador)
            ->put(route('capacitador.cursos.modulos.update', [$curso, $modulo]), [
                'titulo' => $modulo->titulo,
                'contenido' => '<p>Actualizado</p><img src=x onerror="alert(1)">',
            ]);

        $this->assertStringNotContainsString('onerror', $modulo->fresh()->contenido);
        $this->assertStringContainsString('Actualizado', $modulo->fresh()->contenido);
    }

    public function test_worker_view_renders_without_xss(): void
    {
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $trabajador = User::factory()->create(['estamento_id' => $estamento->id]);
        $trabajador->assignRole('Trabajador');

        $curso = Curso::factory()->create();
        $estamento->cursos()->attach($curso);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
        ]);

        // Simula contenido legacy que no pasó por clean() al guardarse
        $modulo = Modulo::factory()->create([
            'curso_id' => $curso->id,
            'tipo_contenido' => 'texto',
            'contenido' => '<p>Contenido legítimo</p><script>alert("legacy xss")</script>',
            'orden' => 1,
        ]);

        $response = $this->actingAs($trabajador)
            ->get(route('modulos.show', [$curso, $modulo]));

        $response->assertOk();
        $response->assertDontSee('<script>alert("legacy xss")</script>', false);
        $response->assertSee('Contenido legítimo', false);
    }
}
