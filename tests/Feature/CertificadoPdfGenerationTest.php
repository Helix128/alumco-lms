<?php

namespace Tests\Feature;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\GlobalSetting;
use App\Models\Modulo;
use App\Models\User;
use App\Services\CertificadoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CertificadoPdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_generation_creates_record_without_storing_pdf_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $curso = Curso::factory()->create();

        $certificado = app(CertificadoService::class)->generarParaUsuario($user, $curso);

        $this->assertModelExists($certificado);
        $this->assertSame('', $certificado->ruta_pdf);
        Storage::disk('public')->assertDirectoryEmpty('certificados');
    }

    public function test_worker_certificate_download_uses_readable_ascii_filename_and_removes_legacy_stored_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'María Fernanda De Los Ángeles Contreras Valenzuela',
        ]);
        $curso = Curso::factory()->create([
            'titulo' => 'Gestión avanzada de comunidades educativas y prevención de riesgos institucionales',
        ]);
        $legacyPath = 'certificados/legacy.pdf';
        $codigoVerificacion = (string) Str::uuid();

        Storage::disk('public')->put($legacyPath, 'old pdf');

        $certificado = Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'codigo_verificacion' => $codigoVerificacion,
            'ruta_pdf' => $legacyPath,
            'fecha_emision' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('mis-certificados.descargar', $certificado));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $disposition = $response->headers->get('content-disposition');
        $expectedFileName = 'certificado_gestion-avanzada-de-comunidades-educativas-y-prevencion-de-riesgos-institucional_maria-fernanda-de-los-angeles-contreras-valenzuela.pdf';

        $this->assertIsString($disposition);
        $this->assertStringContainsString($expectedFileName, $disposition);
        $this->assertStringNotContainsString($codigoVerificacion, $disposition);
        $this->assertStringContainsString('%PDF', $response->streamedContent());
        $this->assertSame(36, strlen($codigoVerificacion));
        Storage::disk('public')->assertMissing($legacyPath);
        $this->assertSame('', $certificado->refresh()->ruta_pdf);
    }

    public function test_capacitador_certificate_download_uses_same_readable_filename(): void
    {
        Role::firstOrCreate(['name' => 'Capacitador Interno']);

        $capacitador = User::factory()->create();
        $capacitador->assignRole('Capacitador Interno');
        $user = User::factory()->create([
            'name' => 'María Fernanda Contreras',
        ]);
        $curso = Curso::factory()->create([
            'titulo' => 'Gestión avanzada de comunidades',
            'capacitador_id' => $capacitador->id,
        ]);
        $certificado = Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'codigo_verificacion' => (string) Str::uuid(),
            'ruta_pdf' => '',
            'fecha_emision' => now(),
        ]);

        $response = $this
            ->actingAs($capacitador)
            ->get(route('capacitador.certificados.descargar', $certificado));

        $response->assertOk();

        $disposition = $response->headers->get('content-disposition');

        $this->assertIsString($disposition);
        $this->assertStringContainsString(
            'certificado_gestion-avanzada-de-comunidades_maria-fernanda-contreras.pdf',
            $disposition
        );
        $this->assertStringNotContainsString($certificado->codigo_verificacion, $disposition);
    }

    public function test_certificate_download_renders_with_capacitador_and_institutional_signatures(): void
    {
        Storage::fake('public');
        Role::firstOrCreate(['name' => 'Capacitador Interno']);

        $capacitadorSignature = UploadedFile::fake()->image('firma-capacitador.png');
        $institutionalSignature = UploadedFile::fake()->image('firma-institucional.png');
        $capacitadorPath = Storage::disk('public')->putFileAs('firmas', $capacitadorSignature, 'capacitador.png');
        $institutionalPath = Storage::disk('public')->putFileAs('firmas', $institutionalSignature, 'institucional.png');

        $capacitador = User::factory()->create([
            'firma_digital' => $capacitadorPath,
        ]);
        $capacitador->assignRole('Capacitador Interno');
        $user = User::factory()->create();
        $curso = Curso::factory()->create([
            'capacitador_id' => $capacitador->id,
        ]);
        $certificado = Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'codigo_verificacion' => (string) Str::uuid(),
            'ruta_pdf' => '',
            'fecha_emision' => now(),
        ]);

        GlobalSetting::set('firma_representante_legal', $institutionalPath);

        $response = $this
            ->actingAs($capacitador)
            ->get(route('capacitador.certificados.descargar', $certificado));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('%PDF', $response->streamedContent());
    }

    public function test_certificate_download_filename_uses_fallbacks_for_missing_text(): void
    {
        $user = User::factory()->create(['name' => '']);
        $curso = Curso::factory()->create(['titulo' => '']);
        $certificado = Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'codigo_verificacion' => (string) Str::uuid(),
            'ruta_pdf' => '',
            'fecha_emision' => now(),
        ]);

        $fileName = app(CertificadoService::class)->downloadFileName($certificado);

        $this->assertSame('certificado_curso_participante.pdf', $fileName);
        $this->assertStringNotContainsString($certificado->codigo_verificacion, $fileName);
    }

    public function test_capacitador_sees_domain_message_when_worker_has_not_approved_evaluation(): void
    {
        Role::firstOrCreate(['name' => 'Capacitador Interno']);

        $capacitador = User::factory()->create();
        $capacitador->assignRole('Capacitador Interno');
        $trabajador = User::factory()->create(['name' => 'Jose Alvarez']);
        $curso = Curso::factory()->create(['capacitador_id' => $capacitador->id]);
        $modulo = Modulo::factory()->evaluacion()->create(['curso_id' => $curso->id]);
        Evaluacion::factory()->create(['modulo_id' => $modulo->id]);

        $this
            ->actingAs($capacitador)
            ->post(route('capacitador.certificados.generar', [$curso, $trabajador]))
            ->assertRedirect()
            ->assertSessionHas('error', 'El certificado no se puede generar porque el trabajador aun no aprueba la evaluacion requerida del curso.');
    }
}
