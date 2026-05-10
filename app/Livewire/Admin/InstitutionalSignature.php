<?php

namespace App\Livewire\Admin;

use App\Models\GlobalSetting;
use App\Support\Signatures\SignatureImage;
use Livewire\Component;
use Livewire\WithFileUploads;

class InstitutionalSignature extends Component
{
    use WithFileUploads;

    public $firma_representante_legal;

    public string $firma_actual = '';

    public ?string $mensaje = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAdminAccess(), 403);

        $this->firma_actual = GlobalSetting::get('firma_representante_legal', '');
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->hasAdminAccess(), 403);

        $this->validate([
            'firma_representante_legal' => SignatureImage::rules(),
        ]);

        SignatureImage::delete($this->firma_actual);

        $path = $this->firma_representante_legal->store(SignatureImage::Directory, 'public');

        GlobalSetting::set('firma_representante_legal', $path);

        $this->firma_actual = $path;
        $this->firma_representante_legal = null;
        $this->mensaje = 'Firma institucional actualizada correctamente.';
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.admin.institutional-signature');
    }
}
