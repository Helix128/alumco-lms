<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CapacitadorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_capacitador_dashboard_uses_cacheable_array_course_summaries(): void
    {
        $capacitador = $this->createCapacitador();
        Cache::forget("dashboard_summary_v2_capacitador_{$capacitador->id}");

        $programado = Curso::factory()->create([
            'capacitador_id' => $capacitador->id,
            'titulo' => 'Curso programado',
            'created_at' => now()->subMinute(),
        ]);
        Curso::factory()->create([
            'capacitador_id' => $capacitador->id,
            'titulo' => 'Curso sin programar',
            'created_at' => now(),
        ]);

        PlanificacionCurso::create([
            'curso_id' => $programado->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addWeek()->toDateString(),
        ]);

        $this
            ->actingAs($capacitador)
            ->get(route('capacitador.dashboard'))
            ->assertOk()
            ->assertSee('Curso programado')
            ->assertSee('Curso sin programar')
            ->assertSee('Programado')
            ->assertSee('Sin Programar')
            ->assertSee('Iniciaron')
            ->assertSee('Completaron')
            ->assertSee('En riesgo');

        $this
            ->actingAs($capacitador)
            ->get(route('capacitador.dashboard'))
            ->assertOk()
            ->assertSee('Curso programado')
            ->assertSee('Curso sin programar')
            ->assertSee('Programado')
            ->assertSee('Sin Programar');
    }

    public function test_capacitador_dashboard_only_lists_own_courses(): void
    {
        $capacitador = $this->createCapacitador();
        $otroCapacitador = $this->createCapacitador();
        Cache::forget("dashboard_summary_v2_capacitador_{$capacitador->id}");

        Curso::factory()->create([
            'capacitador_id' => $capacitador->id,
            'titulo' => 'Curso visible',
        ]);
        Curso::factory()->create([
            'capacitador_id' => $otroCapacitador->id,
            'titulo' => 'Curso ajeno',
        ]);

        $this
            ->actingAs($capacitador)
            ->get(route('capacitador.dashboard'))
            ->assertOk()
            ->assertSee('Curso visible')
            ->assertDontSee('Curso ajeno');
    }

    private function createCapacitador(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Capacitador Interno');

        return $user;
    }
}
