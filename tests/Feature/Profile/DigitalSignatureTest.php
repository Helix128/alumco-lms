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

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
