<?php

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class HelpCenterTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_public_help_only_exposes_public_topics(): void
    {
        $this->get(route('help.index'))
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertSee('Verificar un certificado')
            ->assertDontSee('Herramientas técnicas')
            ->assertDontSee('Reportes');

        $this->get(route('help.show', 'reportes'))->assertNotFound();
        $this->get(route('help.show', 'iniciar-sesion'))->assertOk();
    }

    public function test_topics_are_filtered_by_role_without_leaking_unauthorized_content(): void
    {
        $worker = $this->createTrabajador();
        $admin = $this->createAdmin();
        $developer = $this->createDev();

        $this->actingAs($worker)->get(route('help.index'))
            ->assertSee('Mis capacitaciones')
            ->assertDontSee('Crear contenido')
            ->assertDontSee('Reportes');

        $this->actingAs($admin)->get(route('help.show', 'reportes'))->assertOk();
        $this->actingAs($admin)->get(route('help.show', 'herramientas-tecnicas'))->assertNotFound();
        $this->actingAs($developer)->get(route('help.show', 'herramientas-tecnicas'))->assertOk();
    }

    public function test_help_search_reports_results_and_an_actionable_empty_state(): void
    {
        $this->get(route('help.index', ['buscar' => 'contraseña']))
            ->assertOk()
            ->assertSee('Recuperar el acceso');

        $this->get(route('help.index', ['buscar' => 'palabra-inexistente']))
            ->assertOk()
            ->assertSee('No encontramos un tema con esas palabras')
            ->assertSee('contactar a soporte');
    }
}
