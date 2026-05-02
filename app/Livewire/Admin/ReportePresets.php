<?php

namespace App\Livewire\Admin;

use App\Models\ReportePreset;
use Livewire\Component;

class ReportePresets extends Component
{
    public $presets;

    public $nuevoNombre = '';

    public function mount()
    {
        $this->cargarPresets();
    }

    public function cargarPresets()
    {
        $this->presets = ReportePreset::orderBy('nombre')->get();
    }

    public function guardarPreset(array $columnas)
    {
        $this->validate([
            'nuevoNombre' => 'required|string|max:50|unique:reporte_presets,nombre',
        ], [
            'nuevoNombre.required' => 'Debes asignar un nombre al formato.',
            'nuevoNombre.unique' => 'Ya existe un formato con este nombre.',
            'nuevoNombre.max' => 'El nombre es muy largo (máx 50 carac.).',
        ]);

        ReportePreset::create([
            'nombre' => $this->nuevoNombre,
            'columnas' => $columnas,
        ]);

        $this->nuevoNombre = '';
        $this->cargarPresets();

        $this->dispatch('preset-guardado');
    }

    public function eliminarPreset(int $id)
    {
        ReportePreset::destroy($id);
        $this->cargarPresets();
    }

    public function resetError($field = null)
    {
        $this->resetValidation($field);
    }

    public function render()
    {
        return view('livewire.admin.reporte-presets');
    }
}
