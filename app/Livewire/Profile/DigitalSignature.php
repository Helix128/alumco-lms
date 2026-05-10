<?php

namespace App\Livewire\Profile;

use App\Support\Signatures\SignatureImage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DigitalSignature extends Component
{
    use WithFileUploads;

    public $firma_digital;

    public string $firma_actual = '';

    public ?string $mensaje = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAdminAccess() || auth()->user()?->isCapacitador(), 403);

        $this->firma_actual = auth()->user()->firma_digital ?? '';
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->hasAdminAccess() || auth()->user()?->isCapacitador(), 403);

        $this->validate([
            'firma_digital' => SignatureImage::rules(),
        ]);

        $user = auth()->user();

        SignatureImage::delete($user->firma_digital);

        $path = $this->firma_digital->store(SignatureImage::Directory, 'public');

        $user->forceFill([
            'firma_digital' => $path,
        ])->save();

        $this->firma_actual = $path;
        $this->firma_digital = null;
        $this->mensaje = 'Firma personal actualizada correctamente.';
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.profile.digital-signature');
    }
}
