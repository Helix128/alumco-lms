<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\PlanificacionCurso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_page_renders_accessibility_options_and_compactable_cards(): void
    {
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $user = User::factory()->create(['estamento_id' => $estamento->id]);
        $curso = Curso::factory()->create(['titulo' => 'Seguridad operacional']);

        $estamento->cursos()->attach($curso);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('cursos.index'))
            ->assertOk()
            ->assertSee('Opciones')
            ->assertSee('Alto contraste')
            ->assertSee('Fondo simple')
            ->assertSee('Tarjetas compactas')
            ->assertSee('worker-course-grid', false)
            ->assertSee('worker-course-tile', false);
    }

    public function test_profile_page_renders_persistent_accessibility_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('perfil.index'))
            ->assertOk()
            ->assertSee('Preferencias de accesibilidad')
            ->assertSee('Son las mismas opciones del botón Opciones')
            ->assertSee('Reducir movimiento')
            ->assertSee('Tamaño de texto');
    }
}
