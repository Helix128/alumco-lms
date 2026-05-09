<?php

namespace Tests\Feature;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificadoVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_verification_form_can_be_viewed(): void
    {
        $response = $this->get(route('certificados.verificar.index'));

        $response->assertOk();
        $response->assertSeeText('Verificar certificado');
        $response->assertSeeText('Código de verificación');
    }

    public function test_public_verification_displays_minimal_certificate_data(): void
    {
        $user = User::factory()->create([
            'name' => 'María González',
            'email' => 'maria@example.test',
            'rut' => '12.345.678-5',
        ]);
        $curso = Curso::factory()->create([
            'titulo' => 'Prevención de Riesgos',
        ]);
        $certificado = Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'codigo_verificacion' => '123e4567-e89b-12d3-a456-426614174000',
            'ruta_pdf' => '',
            'fecha_emision' => now()->setDate(2026, 4, 15),
        ]);

        $response = $this->get(route('certificados.verificar.show', strtoupper($certificado->codigo_verificacion)));

        $response->assertOk();
        $response->assertSeeText('Certificado válido');
        $response->assertSeeText('María González');
        $response->assertSeeText('Prevención de Riesgos');
        $response->assertSeeText('15/04/2026');
        $response->assertSeeText($certificado->codigo_verificacion);
        $response->assertSeeText('Verificar otro documento');
        $response->assertDontSeeText('maria@example.test');
        $response->assertDontSeeText('12.345.678-5');
    }

    public function test_public_verification_query_redirects_to_normalized_code(): void
    {
        $response = $this->get(route('certificados.verificar.index', [
            'codigo' => '  ABC-123  ',
        ]));

        $response->assertRedirect(route('certificados.verificar.show', 'abc-123'));
    }

    public function test_public_verification_shows_invalid_state_with_search_form(): void
    {
        $response = $this->get(route('certificados.verificar.show', 'codigo-inexistente'));

        $response->assertOk();
        $response->assertSeeText('No validado');
        $response->assertSeeText('No encontramos un certificado con ese código');
        $response->assertSeeText('Código de verificación');
    }
}
