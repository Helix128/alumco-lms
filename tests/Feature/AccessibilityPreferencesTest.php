<?php

namespace Tests\Feature;

use App\Livewire\AccessibilityPreferences;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\PlanificacionCurso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccessibilityPreferencesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_courses_page_renders_accessibility_options(): void
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
            ->assertSee('Tamaño de texto')
            ->assertDontSee('Fondo simple')
            ->assertDontSee('Tarjetas compactas');
    }

    public function test_user_layout_applies_saved_accessibility_preferences(): void
    {
        $user = User::factory()->create([
            'accessibility_preferences' => [
                'fontLevel' => 2,
                'highContrast' => true,
                'reducedMotion' => true,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('perfil.index'))
            ->assertOk()
            ->assertSee('data-contrast="high"', false)
            ->assertSee('data-motion="reduced"', false)
            ->assertDontSee('data-background=', false)
            ->assertDontSee('data-cards=', false)
            ->assertSee('--font-base: 22px', false);
    }

    public function test_admin_panel_renders_accessibility_options(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $this->actingAs($admin)
            ->get(route('admin.perfil.index'))
            ->assertOk()
            ->assertSee('Opciones')
            ->assertSee('Preferencias de accesibilidad')
            ->assertSee('Son las mismas opciones del botón Opciones');
    }

    public function test_livewire_component_persists_accessibility_preferences(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AccessibilityPreferences::class)
            ->set('highContrast', true)
            ->set('reducedMotion', true)
            ->call('increaseFont')
            ->call('increaseFont')
            ->assertSet('fontLevel', 2)
            ->assertDispatched('accessibility-preferences-updated')
            ->assertHasNoErrors();

        $this->assertSame([
            'fontLevel' => 2,
            'highContrast' => true,
            'reducedMotion' => true,
        ], $user->refresh()->accessibility_preferences);
    }

    public function test_auth_pages_do_not_render_account_accessibility_preferences(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('accessibility-preferences')
            ->assertDontSee('Opciones');
    }
}
