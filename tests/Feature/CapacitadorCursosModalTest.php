<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapacitadorCursosModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_index_includes_duplicate_modal_script_safe_for_livewire_navigation(): void
    {
        $capacitador = $this->createCapacitador();

        Curso::factory()->create(['capacitador_id' => $capacitador->id]);

        $this
            ->actingAs($capacitador)
            ->get(route('capacitador.cursos.index'))
            ->assertOk()
            ->assertSee('Duplicar capacitación')
            ->assertSee('window.alumcoDuplicateModal', false)
            ->assertDontSee('let isDupOpen', false);
    }

    public function test_show_includes_duplicate_modal_script_when_opened_directly(): void
    {
        $capacitador = $this->createCapacitador();

        $curso = Curso::factory()->create(['capacitador_id' => $capacitador->id]);

        $this
            ->actingAs($capacitador)
            ->get(route('capacitador.cursos.show', $curso))
            ->assertOk()
            ->assertSee('Duplicar capacitación')
            ->assertSee('window.openDuplicateModal', false)
            ->assertDontSee('let isDupOpen', false);
    }

    private function createCapacitador(): User
    {
        $estamento = Estamento::create(['nombre' => 'Capacitación']);

        $capacitador = User::factory()->create([
            'activo' => true,
            'estamento_id' => $estamento->id,
        ]);

        $capacitador->assignRole('Capacitador Interno');

        return $capacitador;
    }
}
