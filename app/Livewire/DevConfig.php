<?php

namespace App\Livewire;

use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DevConfig extends Component
{
    use WithFileUploads;

    public $puntos_aprobacion;

    public $max_intentos_semanales;

    public $firma_rep_legal; // Nueva propiedad para el archivo

    public $firma_actual;    // Ruta de la firma guardada

    public $mensaje;

    public function mount()
    {
        $this->puntos_aprobacion = GlobalSetting::get('evaluacion_puntos_aprobacion', 70);
        $this->max_intentos_semanales = GlobalSetting::get('evaluacion_max_intentos_semanales', 3);
        $this->firma_actual = GlobalSetting::get('firma_representante_legal', '');
    }

    public function guardar()
    {
        $this->validate([
            'puntos_aprobacion' => 'required|integer|min:0|max:100',
            'max_intentos_semanales' => 'required|integer|min:1|max:50',
            'firma_rep_legal' => 'nullable|image|max:1024', // Max 1MB
        ]);

        GlobalSetting::set('evaluacion_puntos_aprobacion', $this->puntos_aprobacion);
        GlobalSetting::set('evaluacion_max_intentos_semanales', $this->max_intentos_semanales);

        if ($this->firma_rep_legal) {
            // Eliminar antigua si existe
            if ($this->firma_actual) {
                Storage::disk('public')->delete($this->firma_actual);
            }
            $path = $this->firma_rep_legal->store('firmas', 'public');
            GlobalSetting::set('firma_representante_legal', $path);
            $this->firma_actual = $path;
            $this->firma_rep_legal = null; // Limpiar input
        }

        $this->mensaje = 'Configuración guardada exitosamente.';
    }

    public function render()
    {
        return view('livewire.dev-config');
    }
}
