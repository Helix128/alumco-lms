<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Modulo;
use App\Models\ProgresoModulo;
use App\Models\User;
use App\Services\Cursos\CourseModuleLoader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseModuleLoaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_ordered_modules_with_user_progress(): void
    {
        $user = User::factory()->create();
        $curso = Curso::factory()->create();
        $second = Modulo::factory()->create(['curso_id' => $curso->id, 'orden' => 2]);
        $first = Modulo::factory()->create(['curso_id' => $curso->id, 'orden' => 1]);
        ProgresoModulo::create([
            'user_id' => $user->id,
            'modulo_id' => $first->id,
            'completado' => true,
            'fecha_completado' => now(),
        ]);

        app(CourseModuleLoader::class)->loadForUser($curso, $user);

        $this->assertTrue($curso->relationLoaded('modulos'));
        $this->assertSame([$first->id, $second->id], $curso->modulos->pluck('id')->all());
        $this->assertTrue($curso->modulos->first()->relationLoaded('progresos'));
        $this->assertCount(1, $curso->modulos->first()->progresos);
    }
}
