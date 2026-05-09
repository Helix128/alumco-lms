<?php

namespace App\Livewire;

use App\Models\GlobalSetting;
use Livewire\Component;

class DevConfig extends Component
{
    public $puntos_aprobacion;

    public $max_intentos_semanales;

    public $mensaje;

    public function mount()
    {
        $this->puntos_aprobacion = GlobalSetting::get('evaluacion_puntos_aprobacion', 70);
        $this->max_intentos_semanales = GlobalSetting::get('evaluacion_max_intentos_semanales', 3);
    }

    public function guardar()
    {
        $this->validate([
            'puntos_aprobacion' => 'required|integer|min:0|max:100',
            'max_intentos_semanales' => 'required|integer|min:1|max:50',
        ]);

        GlobalSetting::set('evaluacion_puntos_aprobacion', $this->puntos_aprobacion);
        GlobalSetting::set('evaluacion_max_intentos_semanales', $this->max_intentos_semanales);

        $this->mensaje = 'Configuración guardada exitosamente.';
    }

    public function render()
    {
        return view('livewire.dev-config');
    }
}
