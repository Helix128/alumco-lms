<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_sees_grouped_navigation_and_institutional_signature_link(): void
    {
        $admin = $this->userWithRole('Administrador');

        $this
            ->actingAs($admin)
            ->get(route('admin.perfil.index'))
            ->assertOk()
            ->assertSee('Estadísticas')
            ->assertSee('Material')
            ->assertSee('Gestión')
            ->assertSee('Dashboard analítico')
            ->assertSee('Reportes de capacitación')
            ->assertSee('Directorio de usuarios')
            ->assertSee('Estamentos')
            ->assertSee('Firma institucional')
            ->assertDontSee('Dashboard capacitador')
            ->assertDontSee('Desarrollador');
    }

    public function test_panel_profile_prioritizes_signature_without_duplicate_options_or_warning(): void
    {
        $admin = $this->userWithRole('Administrador');

        $this
            ->actingAs($admin)
            ->get(route('admin.perfil.index'))
            ->assertOk()
            ->assertSee('Perfil y firma')
            ->assertSee('Firma para Certificados')
            ->assertSee('Subir Archivo')
            ->assertDontSee('Cuenta Administrativa')
            ->assertDontSee('Son las mismas opciones del botón Opciones');
    }

    public function test_persistent_topbar_syncs_header_title_from_current_panel_view(): void
    {
        $admin = $this->userWithRole('Administrador');

        $this
            ->actingAs($admin)
            ->get(route('admin.dashboard.index'))
            ->assertOk()
            ->assertSee('x-on:livewire:navigated.document', false)
            ->assertSee('x-text="title"', false)
            ->assertSee('data-admin-header-title="Dashboard Analítico"', false);

        $this
            ->actingAs($admin)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('data-admin-header-title="Gestión de Colaboradores"', false);
    }

    public function test_capacitador_sees_panel_groups_without_developer_or_institutional_links(): void
    {
        $capacitador = $this->userWithRole('Capacitador Interno');

        $this
            ->actingAs($capacitador)
            ->get(route('capacitador.dashboard'))
            ->assertOk()
            ->assertSee('Estadísticas')
            ->assertSee('Material')
            ->assertSee('Gestión')
            ->assertSee('Dashboard capacitador')
            ->assertSee('Capacitaciones y material')
            ->assertSee('Perfil y firma')
            ->assertDontSee('Dashboard analítico')
            ->assertDontSee('Directorio de usuarios')
            ->assertDontSee('Desarrollador')
            ->assertDontSee('Firma institucional');
    }

    public function test_developer_sees_admin_navigation_plus_developer_navigation(): void
    {
        $developer = $this->userWithRole('Desarrollador');

        $this
            ->actingAs($developer)
            ->get(route('admin.perfil.index'))
            ->assertOk()
            ->assertSee('Perfil y firma')
            ->assertSee('Dashboard analítico')
            ->assertSee('Reportes de capacitación')
            ->assertSee('Directorio de usuarios')
            ->assertSee('Estamentos')
            ->assertSee('Capacitaciones y material')
            ->assertSee('Firma institucional')
            ->assertSee('Desarrollador')
            ->assertSee('Lógica de negocio')
            ->assertSee('Salud LMS')
            ->assertSee('Soporte técnico')
            ->assertDontSee('Dashboard capacitador');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
