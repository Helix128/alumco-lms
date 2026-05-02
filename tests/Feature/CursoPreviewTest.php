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

class CursoPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_preview_includes_estamento_courses_even_when_not_active(): void
    {
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $user = User::factory()->create(['estamento_id' => $estamento->id]);
        $user->assignRole('Trabajador');

        $curso = Curso::factory()->create();
        $estamento->cursos()->attach($curso);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => now()->subMonth()->startOfMonth()->toDateString(),
            'fecha_fin' => now()->subMonth()->endOfMonth()->toDateString(),
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['preview_mode' => true])
            ->get(route('cursos.index'));

        $response->assertOk();

        $titulosVigentes = $response->viewData('vigentes')->pluck('titulo')->all();

        $this->assertContains($curso->titulo, $titulosVigentes);
    }

    public function test_preview_allows_opening_estamento_course_even_when_not_active(): void
    {
        $estamento = Estamento::create(['nombre' => 'Administración']);
        $user = User::factory()->create(['estamento_id' => $estamento->id]);
        $user->assignRole('Trabajador');

        $curso = Curso::factory()->create();
        $estamento->cursos()->attach($curso);

        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'fecha_inicio' => now()->subMonth()->startOfMonth()->toDateString(),
            'fecha_fin' => now()->subMonth()->endOfMonth()->toDateString(),
        ]);

        $this
            ->actingAs($user)
            ->withSession(['preview_mode' => true])
            ->get(route('cursos.show', $curso))
            ->assertOk();
    }

    public function test_admin_preview_includes_all_courses_without_date_conditions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $curso = Curso::factory()->create();

        $response = $this
            ->actingAs($admin)
            ->withSession(['preview_mode' => true])
            ->get(route('cursos.index'));

        $response->assertOk();

        $titulosVigentes = $response->viewData('vigentes')->pluck('titulo')->all();

        $this->assertContains($curso->titulo, $titulosVigentes);
    }

    public function test_admin_preview_can_view_course_pdf_inline_without_public_storage_url(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $curso = Curso::factory()->create();
        $modulo = Modulo::factory()->create([
            'curso_id' => $curso->id,
            'orden' => 1,
            'tipo_contenido' => 'pdf',
            'ruta_archivo' => 'documentos/checklist-iaas.pdf',
            'nombre_archivo_original' => 'checklist-iaas.pdf',
        ]);

        Storage::disk('public')->put($modulo->ruta_archivo, 'pdf demo');

        $this
            ->actingAs($admin)
            ->withSession(['preview_mode' => true])
            ->get(route('modulos.show', [$curso, $modulo]))
            ->assertOk()
            ->assertSee(route('modulos.archivo', [$curso, $modulo]), false)
            ->assertDontSee('/storage/documentos/checklist-iaas.pdf', false);

        $response = $this
            ->actingAs($admin)
            ->withSession(['preview_mode' => true])
            ->get(route('modulos.archivo', [$curso, $modulo]))
            ->assertOk();

        $this->assertStringStartsWith('inline;', $response->headers->get('content-disposition'));
    }

    public function test_worker_without_course_access_cannot_view_module_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('Trabajador');

        $curso = Curso::factory()->create();
        $modulo = Modulo::factory()->create([
            'curso_id' => $curso->id,
            'orden' => 1,
            'tipo_contenido' => 'pdf',
            'ruta_archivo' => 'documentos/checklist-iaas.pdf',
        ]);

        Storage::disk('public')->put($modulo->ruta_archivo, 'pdf demo');

        $this
            ->actingAs($user)
            ->get(route('modulos.archivo', [$curso, $modulo]))
            ->assertForbidden();
    }

    public function test_module_file_from_another_course_returns_not_found(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $curso = Curso::factory()->create();
        $otroCurso = Curso::factory()->create();
        $modulo = Modulo::factory()->create([
            'curso_id' => $otroCurso->id,
            'orden' => 1,
            'tipo_contenido' => 'pdf',
            'ruta_archivo' => 'documentos/checklist-iaas.pdf',
        ]);

        Storage::disk('public')->put($modulo->ruta_archivo, 'pdf demo');

        $this
            ->actingAs($admin)
            ->withSession(['preview_mode' => true])
            ->get(route('modulos.archivo', [$curso, $modulo]))
            ->assertNotFound();
    }

    public function test_missing_module_file_returns_not_found(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $curso = Curso::factory()->create();
        $modulo = Modulo::factory()->create([
            'curso_id' => $curso->id,
            'orden' => 1,
            'tipo_contenido' => 'pdf',
            'ruta_archivo' => 'documentos/checklist-iaas.pdf',
        ]);

        $this
            ->actingAs($admin)
            ->withSession(['preview_mode' => true])
            ->get(route('modulos.archivo', [$curso, $modulo]))
            ->assertNotFound();
    }
}
