<?php

namespace Tests\Feature;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\User;
use App\Services\CertificadoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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

    public function test_certificate_download_streams_pdf_and_removes_legacy_stored_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $curso = Curso::factory()->create();
        $legacyPath = 'certificados/legacy.pdf';

        Storage::disk('public')->put($legacyPath, 'old pdf');

        $certificado = Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'codigo_verificacion' => 'TEST-CERT',
            'ruta_pdf' => $legacyPath,
            'fecha_emision' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('mis-certificados.descargar', $certificado));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $this->assertStringContainsString('%PDF', $response->streamedContent());
        Storage::disk('public')->assertMissing($legacyPath);
        $this->assertSame('', $certificado->refresh()->ruta_pdf);
    }
}
