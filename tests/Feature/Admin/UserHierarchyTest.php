<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UserManagement;
use App\Models\Sede;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class UserHierarchyTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_an_admin_cannot_edit_themselves()
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('edit', $admin->id)
            ->assertForbidden();
    }

    public function test_an_admin_cannot_edit_a_developer()
    {
        $admin = $this->createAdmin();
        $dev = $this->createDev();

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('edit', $dev->id)
            ->assertForbidden();
    }

    public function test_an_admin_cannot_delete_a_developer()
    {
        $admin = $this->createAdmin();
        $dev = $this->createDev();

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('deleteUser', $dev->id)
            ->assertForbidden();
    }

    public function test_an_admin_cannot_assign_the_developer_role()
    {
        $admin = $this->createAdmin();
        $trabajador = $this->createTrabajador();
        $sede = Sede::create(['nombre' => 'Sede Test']);

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('editingUser', $trabajador)
            ->set('name', 'Nombre Editado')
            ->set('email', 'email@test.com')
            ->set('role', 'Desarrollador')
            ->set('sede_id', $sede->id)
            ->call('save')
            ->assertForbidden();
    }

    public function test_a_developer_can_access_admin_user_management_and_edit_an_admin()
    {
        $dev = $this->createDev();
        $admin = $this->createAdmin();

        $this
            ->actingAs($dev)
            ->get(route('admin.usuarios.index'))
            ->assertOk();

        Livewire::actingAs($dev)
            ->test(UserManagement::class)
            ->call('edit', $admin->id)
            ->assertOk()
            ->assertSet('name', $admin->name);
    }

    public function test_guest_users_cannot_access_user_management()
    {
        $this->get(route('admin.usuarios.index'))
            ->assertRedirect(route('login'));
    }

    public function test_workers_cannot_access_user_management()
    {
        $trabajador = $this->createTrabajador();

        $this->actingAs($trabajador)
            ->get(route('admin.usuarios.index'))
            ->assertForbidden();
    }
}
