<?php

namespace Tests\Feature\Profile;

use App\Livewire\Profile\DigitalSignature;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalSignatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_capacitador_can_upload_own_digital_signature(): void
    {
        Storage::fake('public');
        $capacitador = $this->userWithRole('Capacitador Interno');
        Storage::disk('public')->put('firmas/anterior.png', 'firma anterior');
        $capacitador->update(['firma_digital' => 'firmas/anterior.png']);

        Livewire::actingAs($capacitador)
            ->test(DigitalSignature::class)
            ->set('firma_digital', UploadedFile::fake()->image('firma.png'))
            ->call('guardar')
            ->assertHasNoErrors();

        $capacitador->refresh();

        $this->assertNotNull($capacitador->firma_digital);
        $this->assertNotSame('firmas/anterior.png', $capacitador->firma_digital);
        Storage::disk('public')->assertExists($capacitador->firma_digital);
        Storage::disk('public')->assertMissing('firmas/anterior.png');
    }

    public function test_admin_can_upload_own_digital_signature_from_panel_profile(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('Administrador');

        Livewire::actingAs($admin)
            ->test(DigitalSignature::class)
            ->set('firma_digital', UploadedFile::fake()->image('firma-admin.png'))
            ->call('guardar')
            ->assertHasNoErrors();

        $admin->refresh();

        $this->assertNotNull($admin->firma_digital);
        Storage::disk('public')->assertExists($admin->firma_digital);
    }

    public function test_developer_can_upload_own_digital_signature_from_panel_profile(): void
    {
        Storage::fake('public');
        $developer = $this->userWithRole('Desarrollador');

        Livewire::actingAs($developer)
            ->test(DigitalSignature::class)
            ->set('firma_digital', UploadedFile::fake()->image('firma-dev.png'))
            ->call('guardar')
            ->assertHasNoErrors();

        $developer->refresh();

        $this->assertNotNull($developer->firma_digital);
        Storage::disk('public')->assertExists($developer->firma_digital);
    }

    public function test_worker_cannot_access_digital_signature_component(): void
    {
        $trabajador = $this->userWithRole('Trabajador');

        Livewire::actingAs($trabajador)
            ->test(DigitalSignature::class)
            ->assertForbidden();
    }

    public function test_digital_signature_requires_a_supported_image(): void
    {
        $capacitador = $this->userWithRole('Capacitador Externo');

        Livewire::actingAs($capacitador)
            ->test(DigitalSignature::class)
            ->set('firma_digital', UploadedFile::fake()->create('firma.pdf', 64, 'application/pdf'))
            ->call('guardar')
            ->assertHasErrors(['firma_digital']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
