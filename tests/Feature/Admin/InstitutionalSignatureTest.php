<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\InstitutionalSignature;
use App\Models\GlobalSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InstitutionalSignatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_upload_institutional_signature(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('Administrador');
        Storage::disk('public')->put('firmas/anterior.png', 'firma anterior');
        GlobalSetting::set('firma_representante_legal', 'firmas/anterior.png');

        Livewire::actingAs($admin)
            ->test(InstitutionalSignature::class)
            ->set('firma_representante_legal', UploadedFile::fake()->image('firma.png'))
            ->call('guardar')
            ->assertHasNoErrors();

        $path = GlobalSetting::get('firma_representante_legal');

        $this->assertIsString($path);
        $this->assertNotSame('firmas/anterior.png', $path);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing('firmas/anterior.png');
    }

    public function test_capacitador_cannot_access_institutional_signature(): void
    {
        $capacitador = $this->userWithRole('Capacitador Interno');

        $this
            ->actingAs($capacitador)
            ->get(route('admin.acreditacion.index'))
            ->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
