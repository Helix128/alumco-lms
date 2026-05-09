<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Modulo;
use App\Models\PlanificacionCurso;
use App\Models\ProgresoModulo;
use App\Models\User;
use App\Services\Cursos\ModuleAccessService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ModuleAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_it_rejects_worker_access_to_locked_module(): void
    {
        [$user, $curso, $lockedModule] = $this->courseWithLockedSecondModule();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Este módulo aún está bloqueado.');

        app(ModuleAccessService::class)->authorizeWorkerAccess($curso, $lockedModule, $user);
    }

    public function test_it_returns_loaded_module_when_sequence_is_complete(): void
    {
        [$user, $curso, $module, $firstModule] = $this->courseWithLockedSecondModule();
        ProgresoModulo::create([
            'user_id' => $user->id,
            'modulo_id' => $firstModule->id,
            'completado' => true,
            'fecha_completado' => now(),
        ]);

        $authorizedModule = app(ModuleAccessService::class)->authorizeWorkerAccess($curso, $module, $user);

        $this->assertTrue($authorizedModule->is($module));
        $this->assertTrue($curso->relationLoaded('modulos'));
    }

    /**
     * @return array{0: User, 1: Curso, 2: Modulo, 3: Modulo}
     */
    private function courseWithLockedSecondModule(): array
    {
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $user = User::factory()->create(['estamento_id' => $estamento->id]);
        $user->assignRole('Trabajador');
        $curso = Curso::factory()->create();
        $estamento->cursos()->attach($curso);
        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(),
        ]);
        $firstModule = Modulo::factory()->create([
            'curso_id' => $curso->id,
            'orden' => 1,
        ]);
        $lockedModule = Modulo::factory()->create([
            'curso_id' => $curso->id,
            'orden' => 2,
        ]);

        return [$user, $curso, $lockedModule, $firstModule];
    }
}
