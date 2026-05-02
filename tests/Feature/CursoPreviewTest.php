<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Modulo;
use App\Models\PlanificacionCurso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->assertSee(route('modulos.archivo', [$curso, $modulo], false), false)
            ->assertSee('data-module-pdf-viewer', false)
            ->assertDontSee('/storage/documentos/checklist-iaas.pdf', false);

        $response = $this
            ->actingAs($admin)
            ->withSession(['preview_mode' => true])
            ->get(route('modulos.archivo', [$curso, $modulo]))
            ->assertOk();

        $this->assertStringStartsWith('inline;', $response->headers->get('content-disposition'));
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertSame('nosniff', $response->headers->get('x-content-type-options'));
    }

    public function test_module_files_are_served_inline_with_browser_viewable_mime_types(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $cases = [
            ['pdf', 'documentos/manual.pdf', 'manual.pdf', 'application/pdf'],
            ['ppt', 'presentaciones/induccion.ppt', 'induccion.ppt', 'application/vnd.ms-powerpoint'],
            ['ppt', 'presentaciones/induccion.pptx', 'induccion.pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            ['imagen', 'imagenes/infografia.jpg', 'infografia.jpg', 'image/jpeg'],
            ['imagen', 'imagenes/infografia.jpeg', 'infografia.jpeg', 'image/jpeg'],
            ['imagen', 'imagenes/infografia.png', 'infografia.png', 'image/png'],
            ['imagen', 'imagenes/infografia.gif', 'infografia.gif', 'image/gif'],
            ['imagen', 'imagenes/infografia.webp', 'infografia.webp', 'image/webp'],
            ['video', 'videos/capsula.mp4', 'capsula.mp4', 'video/mp4'],
            ['video', 'videos/capsula.webm', 'capsula.webm', 'video/webm'],
            ['video', 'videos/capsula.ogg', 'capsula.ogg', 'video/ogg'],
        ];

        foreach ($cases as [$tipoContenido, $rutaArchivo, $nombreOriginal, $contentType]) {
            $curso = Curso::factory()->create();
            $modulo = Modulo::factory()->create([
                'curso_id' => $curso->id,
                'orden' => 1,
                'tipo_contenido' => $tipoContenido,
                'ruta_archivo' => $rutaArchivo,
                'nombre_archivo_original' => $nombreOriginal,
            ]);

            Storage::disk('public')->put($modulo->ruta_archivo, 'contenido demo');

            $response = $this
                ->actingAs($admin)
                ->withSession(['preview_mode' => true])
                ->get(route('modulos.archivo', [$curso, $modulo]))
                ->assertOk();

            $this->assertStringStartsWith('inline;', $response->headers->get('content-disposition'));
            $this->assertSame($contentType, $response->headers->get('content-type'));
            $this->assertSame('nosniff', $response->headers->get('x-content-type-options'));
        }
    }

    public function test_presentation_modules_are_rendered_in_the_file_viewer(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $curso = Curso::factory()->create();
        $modulo = Modulo::factory()->create([
            'curso_id' => $curso->id,
            'orden' => 1,
            'tipo_contenido' => 'ppt',
            'ruta_archivo' => 'presentaciones/induccion.pptx',
            'nombre_archivo_original' => 'induccion.pptx',
        ]);

        Storage::disk('public')->put($modulo->ruta_archivo, 'pptx demo');

        $this
            ->actingAs($admin)
            ->withSession(['preview_mode' => true])
            ->get(route('modulos.show', [$curso, $modulo]))
            ->assertOk()
            ->assertDontSee('<iframe', false)
            ->assertSee(route('modulos.descargar', [$curso, $modulo], false), false)
            ->assertSee('Descargar presentación')
            ->assertSee('induccion.pptx');
    }

    public function test_capacitador_can_upload_presentation_module_files(): void
    {
        Storage::fake('public');

        $capacitador = User::factory()->create();
        $capacitador->assignRole('Capacitador Interno');

        $curso = Curso::factory()->create([
            'capacitador_id' => $capacitador->id,
        ]);

        $this
            ->actingAs($capacitador)
            ->post(route('capacitador.cursos.modulos.store', $curso), [
                'titulo' => 'Presentación de inducción',
                'tipo_contenido' => 'ppt',
                'duracion_minutos' => 10,
                'ruta_archivo' => UploadedFile::fake()->create(
                    'induccion.pptx',
                    128,
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ),
            ])
            ->assertRedirect(route('capacitador.cursos.show', $curso));

        $modulo = Modulo::query()
            ->where('curso_id', $curso->id)
            ->where('tipo_contenido', 'ppt')
            ->firstOrFail();

        $this->assertSame('induccion.pptx', $modulo->nombre_archivo_original);
        Storage::disk('public')->assertExists($modulo->ruta_archivo);
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
