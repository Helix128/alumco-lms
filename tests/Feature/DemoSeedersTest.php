<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Evaluacion;
use App\Models\IntentoEvaluacion;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\MediaVariant;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\User;
use Database\Seeders\Common\AdminUserSeeder;
use Database\Seeders\Common\EstamentoSeeder;
use Database\Seeders\Common\SedeSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\Testing\DemoCoursesSeeder;
use Database\Seeders\Testing\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_courses_seed_realistic_text_only_courses_and_evaluations(): void
    {
        Storage::fake('local_media');
        $this->seedBaseData();

        $this->seed(DemoCoursesSeeder::class);

        $this->assertSame(5, Curso::query()->count());
        $this->assertSame(20, Modulo::query()->count());
        $this->assertSame(5, Evaluacion::query()->count());
        $this->assertSame(15, Pregunta::query()->count());
        $this->assertSame(45, Opcion::query()->count());
        $this->assertSame(5, PlanificacionCurso::query()->count());

        $tipos = Modulo::query()->distinct()->pluck('tipo_contenido')->sort()->values()->all();
        $this->assertSame(['evaluacion', 'texto'], $tipos);

        Curso::query()->each(function (Curso $curso): void {
            $this->assertNull($curso->imagen_portada);
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $curso->color_promedio);
            $this->assertSame(1, $curso->estamentos()->count());
            $this->assertNull($curso->coverMedia());

            $modules = $curso->modulos()->orderBy('orden')->get();
            $this->assertSame(['texto', 'texto', 'texto', 'evaluacion'], $modules->pluck('tipo_contenido')->all());
            $this->assertTrue($modules->every(fn (Modulo $modulo): bool => $modulo->ruta_archivo === null));
            $this->assertTrue($modules->every(fn (Modulo $modulo): bool => $modulo->contentMedia() === null));
            $this->assertSame(3, $modules[3]->evaluacion->preguntas()->count());
        });

        Curso::query()->orderBy('id')->get()->each(function (Curso $curso): void {
            $planificacion = $curso->planificaciones()->sole();
            $startsAt = now()->setDate(now()->year, 8, 9)->startOfDay();

            $this->assertNull($planificacion->sede_id);
            $this->assertSame($startsAt->toDateString(), $planificacion->fecha_inicio->toDateString());
            $this->assertSame($startsAt->copy()->addDays(6)->toDateString(), $planificacion->fecha_fin->toDateString());
        });

        $this->assertSame(Estamento::query()->count(), Curso::query()->count());
        Estamento::query()->each(fn (Estamento $estamento) => $this->assertSame(1, $estamento->cursos()->count()));
        $this->assertSame(0, MediaAsset::query()->count());
        $this->assertSame(0, MediaVariant::query()->count());
        $this->assertSame(0, MediaAttachment::query()->where('active', true)->count());
    }

    public function test_demo_seeders_are_idempotent(): void
    {
        Storage::fake('local_media');
        $this->seedBaseData();

        $this->seed([DemoCoursesSeeder::class, DemoUsersSeeder::class]);
        $firstCounts = $this->counts();

        $this->seed([DemoCoursesSeeder::class, DemoUsersSeeder::class]);

        $this->assertSame($firstCounts, $this->counts());
        $users = User::query()->where('email', 'like', 'trabajador.demo.%@alumco.local')->orderBy('email')->get();
        $estamentos = Estamento::query()->orderBy('id')->get();

        $this->assertCount(10, $users);
        foreach ($users as $index => $user) {
            $this->assertTrue($user->hasRole('Trabajador'));
            $this->assertSame($estamentos[$index % $estamentos->count()]->id, $user->estamento_id);
        }
    }

    public function test_database_seeder_creates_no_demo_progress_attempts_or_certificates(): void
    {
        Storage::fake('local_media');

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(10, User::query()->where('email', 'like', 'trabajador.demo.%@alumco.local')->count());
        $this->assertSame(0, \App\Models\ProgresoModulo::query()->count());
        $this->assertSame(0, IntentoEvaluacion::query()->count());
        $this->assertSame(0, \App\Models\Certificado::query()->count());
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
            'media_assets' => MediaAsset::query()->count(),
            'media_variants' => MediaVariant::query()->count(),
            'media_attachments' => MediaAttachment::query()->count(),
            'users' => User::query()->where('email', 'like', 'trabajador.demo.%@alumco.local')->count(),
        ];
    }

}
