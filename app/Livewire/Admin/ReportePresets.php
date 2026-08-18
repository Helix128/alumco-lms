<?php

namespace App\Livewire\Admin;

use App\Exceptions\EditHistoryConflict;
use App\Models\ReportePreset;
use App\Services\History\EditHistoryService;
use Livewire\Attributes\Computed;
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

        app(EditHistoryService::class)->captureChange(
            auth()->user(),
            EditHistoryService::Reports,
            EditHistoryService::GlobalScope,
            'Guardar formato de reporte',
            fn () => ReportePreset::create([
                'nombre' => $this->nuevoNombre,
                'columnas' => $columnas,
            ]),
        );

        $this->nuevoNombre = '';
        $this->cargarPresets();
        unset($this->historyState);

        $this->dispatch('preset-guardado');
    }

    public function eliminarPreset(int $id)
    {
        app(EditHistoryService::class)->captureChange(
            auth()->user(),
            EditHistoryService::Reports,
            EditHistoryService::GlobalScope,
            'Eliminar formato de reporte',
            fn () => ReportePreset::destroy($id),
        );
        $this->cargarPresets();
        unset($this->historyState);
    }

    /** @return array{can_undo: bool, can_redo: bool, undo_label: ?string, redo_label: ?string} */
    #[Computed]
    public function historyState(): array
    {
        return app(EditHistoryService::class)->availability(
            auth()->user(),
            EditHistoryService::Reports,
            EditHistoryService::GlobalScope,
        );
    }

    public function deshacer(): void
    {
        $this->travelHistory(false);
    }

    public function rehacer(): void
    {
        $this->travelHistory(true);
    }

    private function travelHistory(bool $redo): void
    {
        try {
            $step = $redo
                ? app(EditHistoryService::class)->redo(auth()->user(), EditHistoryService::Reports, EditHistoryService::GlobalScope)
                : app(EditHistoryService::class)->undo(auth()->user(), EditHistoryService::Reports, EditHistoryService::GlobalScope);
            $this->cargarPresets();
            unset($this->historyState);
            $this->dispatch('alumco-alert', title: $redo ? 'Cambio rehecho' : 'Cambio deshecho', message: $step->label.'.', type: 'success');
        } catch (EditHistoryConflict $exception) {
            $this->dispatch('alumco-alert', title: 'No se pudo aplicar', message: $exception->getMessage(), type: 'error');
        }
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
