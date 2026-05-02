<?php

namespace Tests\Feature;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\IntentoEvaluacion;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\ProgresoModulo;
use App\Models\User;
use Database\Seeders\Common\AdminUserSeeder;
use Database\Seeders\Common\EstamentoSeeder;
use Database\Seeders\Common\SedeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\Testing\DemoCoursesSeeder;
use Database\Seeders\Testing\DemoProgressSeeder;
use Database\Seeders\Testing\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_courses_seed_realistic_courses_modules_covers_and_evaluations(): void
    {
        Storage::fake('public');
        $this->seedBaseData();

        $this->seed(DemoCoursesSeeder::class);

        $this->assertSame(5, Curso::query()->count());
        $this->assertSame(19, Modulo::query()->count());
        $this->assertSame(5, Evaluacion::query()->count());
        $this->assertSame(15, Pregunta::query()->count());
        $this->assertSame(45, Opcion::query()->count());
        $this->assertSame(5, PlanificacionCurso::query()->count());

        $tipos = Modulo::query()->distinct()->pluck('tipo_contenido')->sort()->values()->all();
        $this->assertSame(['evaluacion', 'imagen', 'pdf', 'ppt', 'texto', 'video'], $tipos);

        Curso::query()->each(function (Curso $curso): void {
            $this->assertNotNull($curso->imagen_portada);
            Storage::disk('public')->assertExists($curso->imagen_portada);
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $curso->color_promedio);
        });

        Storage::disk('public')->assertExists('documentos/checklist-iaas.pdf');
        Storage::disk('public')->assertExists('documentos/derechos-paciente.pdf');

        Curso::query()->orderBy('id')->get()->each(function (Curso $curso, int $index): void {
            $planificacion = $curso->planificaciones()->sole();
            $startsAt = now()->startOfWeek()->addWeeks($index);

            $this->assertNull($planificacion->sede_id);
            $this->assertSame($startsAt->toDateString(), $planificacion->fecha_inicio->toDateString());
            $this->assertSame($startsAt->copy()->endOfWeek()->toDateString(), $planificacion->fecha_fin->toDateString());
        });
    }

    public function test_demo_seeders_are_idempotent(): void
    {
        Storage::fake('public');
        $this->seedBaseData();

        $this->seed([DemoCoursesSeeder::class, DemoUsersSeeder::class]);
        $firstCounts = $this->counts();

        $this->seed([DemoCoursesSeeder::class, DemoUsersSeeder::class]);

        $this->assertSame($firstCounts, $this->counts());
        $this->assertSame(64, User::query()->where('email', 'like', 'trabajador.demo.%@alumco.local')->count());
    }

    public function test_demo_progress_creates_partial_progress_attempts_and_lightweight_certificates(): void
    {
        Storage::fake('public');
        $this->seedBaseData();
        $this->seed([DemoCoursesSeeder::class, DemoUsersSeeder::class]);

        $this->seed(DemoProgressSeeder::class);

        $this->assertGreaterThan(0, ProgresoModulo::query()->count());
        $this->assertGreaterThan(0, IntentoEvaluacion::query()->where('aprobado', true)->count());
        $this->assertGreaterThan(0, Certificado::query()->where('ruta_pdf', 'certificados/demo.pdf')->count());

        $attempt = IntentoEvaluacion::query()->where('aprobado', true)->firstOrFail();
        $this->assertEquals($attempt->total_preguntas, $attempt->puntaje);
        $this->assertEquals($attempt->total_preguntas, $attempt->respuestas()->count());
    }

    private function seedBaseData(): void
    {
        $this->seed([
            SedeSeeder::class,
            EstamentoSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function counts(): array
    {
        return [
            'courses' => Curso::query()->count(),
            'modules' => Modulo::query()->count(),
            'evaluations' => Evaluacion::query()->count(),
            'questions' => Pregunta::query()->count(),
            'options' => Opcion::query()->count(),
            'planning' => PlanificacionCurso::query()->count(),
            'users' => User::query()->where('email', 'like', 'trabajador.demo.%@alumco.local')->count(),
        ];
    }
}
