<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAreaAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_trabajador_login_redirects_to_worker_portal(): void
    {
        $user = $this->createUserWithRole('Trabajador');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('cursos.index'));
    }

    public function test_admin_login_redirects_to_admin_reports(): void
    {
        $user = $this->createUserWithRole('Administrador');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard.index'));
    }

    public function test_developer_login_redirects_to_admin_reports(): void
    {
        $user = $this->createUserWithRole('Desarrollador');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard.index'));
    }

    public function test_capacitador_login_redirects_to_capacitador_dashboard(): void
    {
        $user = $this->createUserWithRole('Capacitador Interno');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('capacitador.dashboard'));
    }

    public function test_admin_with_worker_intended_without_preview_redirects_to_admin_reports(): void
    {
        $user = $this->createUserWithRole('Administrador');

        $this
            ->withSession(['url.intended' => route('cursos.index')])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.dashboard.index'));
    }

    public function test_trabajador_with_worker_intended_redirects_to_worker_portal(): void
    {
        $user = $this->createUserWithRole('Trabajador');

        $this
            ->withSession(['url.intended' => route('cursos.index')])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('cursos.index'));
    }

    public function test_admin_without_preview_is_redirected_from_worker_portal(): void
    {
        $user = $this->createUserWithRole('Administrador');

        $this
            ->actingAs($user)
            ->get(route('cursos.index'))
            ->assertRedirect(route('admin.dashboard.index'));
    }

    public function test_capacitador_without_preview_is_redirected_from_worker_portal(): void
    {
        $user = $this->createUserWithRole('Capacitador Interno');

        $this
            ->actingAs($user)
            ->get(route('cursos.index'))
            ->assertRedirect(route('capacitador.dashboard'));
    }

    public function test_admin_with_preview_can_access_worker_portal(): void
    {
        $user = $this->createUserWithRole('Administrador');

        $this
            ->actingAs($user)
            ->withSession(['preview_mode' => true])
            ->get(route('cursos.index'))
            ->assertOk();
    }

    public function test_capacitador_with_preview_can_access_worker_portal(): void
    {
        $user = $this->createUserWithRole('Capacitador Interno');

        $this
            ->actingAs($user)
            ->withSession(['preview_mode' => true])
            ->get(route('cursos.index'))
            ->assertOk();
    }

    public function test_trabajador_can_access_worker_portal(): void
    {
        $user = $this->createUserWithRole('Trabajador');

        $this
            ->actingAs($user)
            ->get(route('cursos.index'))
            ->assertOk();
    }

    public function test_root_redirects_to_canonical_area_for_role(): void
    {
        $this
            ->actingAs($this->createUserWithRole('Administrador'))
            ->get('/')
            ->assertRedirect(route('admin.dashboard.index'));

        $this
            ->actingAs($this->createUserWithRole('Capacitador Interno'))
            ->get('/')
            ->assertRedirect(route('capacitador.dashboard'));

        $this
            ->actingAs($this->createUserWithRole('Trabajador'))
            ->get('/')
            ->assertRedirect(route('cursos.index'));
    }

    public function test_root_redirects_to_worker_portal_when_preview_is_active(): void
    {
        $user = $this->createUserWithRole('Administrador');

        $this
            ->actingAs($user)
            ->withSession(['preview_mode' => true])
            ->get('/')
            ->assertRedirect(route('cursos.index'));
    }

    public function test_disabling_preview_returns_admin_to_admin_reports(): void
    {
        $user = $this->createUserWithRole('Administrador');

        $this
            ->actingAs($user)
            ->withSession(['preview_mode' => true])
            ->post(route('admin.preview.toggle'))
            ->assertRedirect(route('admin.dashboard.index'));
    }

    public function test_disabling_preview_returns_capacitador_to_dashboard(): void
    {
        $user = $this->createUserWithRole('Capacitador Interno');

        $this
            ->actingAs($user)
            ->withSession(['preview_mode' => true])
            ->post(route('admin.preview.toggle'))
            ->assertRedirect(route('capacitador.dashboard'));
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
