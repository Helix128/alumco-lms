<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UserManagement;
use App\Models\Sede;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class UserManagementTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_an_admin_can_view_the_user_list()
    {
        $admin = $this->createAdmin();
        User::factory()->count(5)->create();

        $this->actingAs($admin)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee($admin->name);
    }

    public function test_an_admin_can_search_for_users()
    {
        $admin = $this->createAdmin();
        $userToFind = User::factory()->create(['name' => 'Usuario Especial']);
        $userToIgnore = User::factory()->create(['name' => 'Otro Sujeto']);

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('search', 'Especial')
            ->assertSee('Usuario Especial')
            ->assertDontSee('Otro Sujeto');
    }

    public function test_an_admin_can_create_a_new_worker()
    {
        $admin = $this->createAdmin();
        $sede = Sede::create(['nombre' => 'Sede Test']);

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('name', 'Nuevo Trabajador')
            ->set('email', 'nuevo@alumco.cl')
            ->set('role', 'Trabajador')
            ->set('sede_id', $sede->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'nuevo@alumco.cl']);
    }

    public function test_it_validates_required_fields_when_creating_user()
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_an_admin_can_upload_a_digital_signature_for_a_capacitador()
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $sede = Sede::create(['nombre' => 'Sede Test']);
        $file = UploadedFile::fake()->image('firma.png');

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('name', 'Capacitador Test')
            ->set('email', 'capa@alumco.cl')
            ->set('role', 'Capacitador Interno')
            ->set('sede_id', $sede->id)
            ->set('firma_digital', $file)
            ->call('save');

        $user = User::where('email', 'capa@alumco.cl')->first();
        $this->assertNotNull($user->firma_digital);
        Storage::disk('public')->assertExists($user->firma_digital);
    }

    public function test_an_admin_can_toggle_user_status()
    {
        $admin = $this->createAdmin();
        $user = $this->createTrabajador();
        $initialStatus = $user->activo;

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('toggleStatus', $user->id);

        $user->refresh();
        $this->assertNotEquals($initialStatus, $user->activo);
    }
}
