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
            ->assertSee('Firma institucional')
            ->assertDontSee('Desarrollador');
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
            ->assertSee('Perfil y firma')
            ->assertDontSee('Desarrollador')
            ->assertDontSee('Firma institucional');
    }

    public function test_developer_sees_developer_navigation_group(): void
    {
        $developer = $this->userWithRole('Desarrollador');

        $this
            ->actingAs($developer)
            ->get(route('admin.perfil.index'))
            ->assertOk()
            ->assertSee('Desarrollador')
            ->assertSee('Lógica de negocio')
            ->assertSee('Salud LMS');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
