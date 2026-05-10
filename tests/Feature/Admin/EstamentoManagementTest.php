<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\EstamentoManagement;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class EstamentoManagementTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_open_estamento_management_from_panel_route(): void
    {
        $admin = $this->createAdmin();

        $this
            ->actingAs($admin)
            ->get(route('admin.estamentos.index'))
            ->assertOk()
            ->assertSee('Gestión de Estamentos')
            ->assertSee('Nuevo estamento')
            ->assertSee('Catálogo de estamentos')
            ->assertSee('Colaboradores');
    }

    public function test_developer_can_open_estamento_management_from_admin_dev_menu(): void
    {
        $developer = $this->createDev();

        $this
            ->actingAs($developer)
            ->get(route('admin.estamentos.index'))
            ->assertOk()
            ->assertSee('Gestión de Estamentos')
            ->assertSee('Desarrollador')
            ->assertSee('Estamentos');
    }

    public function test_capacitador_cannot_manage_estamentos(): void
    {
        $capacitador = User::factory()->create();
        $capacitador->assignRole('Capacitador Interno');

        $this
            ->actingAs($capacitador)
            ->get(route('admin.estamentos.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_estamento(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(EstamentoManagement::class)
            ->call('openCreate')
            ->set('nombre', '  Equipo territorial  ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('nombre', '')
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('estamentos', [
            'nombre' => 'Equipo territorial',
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_rename_estamento(): void
    {
        $admin = $this->createAdmin();
        $estamento = Estamento::query()->create(['nombre' => 'Administración']);

        Livewire::actingAs($admin)
            ->test(EstamentoManagement::class)
            ->call('edit', $estamento->id)
            ->assertSet('editingId', $estamento->id)
            ->set('nombre', 'Personal de administración')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('estamentos', [
            'id' => $estamento->id,
            'nombre' => 'Personal de administración',
            'deleted_at' => null,
        ]);
    }

    public function test_admin_cannot_create_duplicate_estamento(): void
    {
        $admin = $this->createAdmin();

        Estamento::query()->create(['nombre' => 'Profesionales']);

        Livewire::actingAs($admin)
            ->test(EstamentoManagement::class)
            ->set('nombre', 'Profesionales')
            ->call('save')
            ->assertHasErrors(['nombre']);
    }

    public function test_admin_can_delete_estamento_without_assignments(): void
    {
        $admin = $this->createAdmin();
        $estamento = Estamento::query()->create(['nombre' => 'Voluntariado']);

        Livewire::actingAs($admin)
            ->test(EstamentoManagement::class)
            ->call('deleteEstamento', $estamento->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('estamentos', [
            'id' => $estamento->id,
        ]);
    }

    public function test_admin_cannot_delete_estamento_assigned_to_users_or_courses(): void
    {
        $admin = $this->createAdmin();
        $estamento = Estamento::query()->create(['nombre' => 'Operaciones']);
        User::factory()->create(['estamento_id' => $estamento->id]);

        Livewire::actingAs($admin)
            ->test(EstamentoManagement::class)
            ->call('deleteEstamento', $estamento->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('estamentos', [
            'id' => $estamento->id,
            'deleted_at' => null,
        ]);

        $estamentoSinUsuarios = Estamento::query()->create(['nombre' => 'Convenios']);
        $curso = Curso::factory()->create(['capacitador_id' => $admin->id]);
        $curso->estamentos()->attach($estamentoSinUsuarios);

        Livewire::actingAs($admin)
            ->test(EstamentoManagement::class)
            ->call('deleteEstamento', $estamentoSinUsuarios->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('estamentos', [
            'id' => $estamentoSinUsuarios->id,
            'deleted_at' => null,
        ]);
    }
}
