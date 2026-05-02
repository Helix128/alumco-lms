<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use App\Models\Estamento;
use Illuminate\Support\Collection;
use Livewire\Component;

class GestionEstamentos extends Component
{
    public Curso $curso;

    public array $seleccionados = [];

    public Collection $todos;

    public function mount(Curso $curso): void
    {
        $this->curso = $curso;
        $this->todos = Estamento::orderBy('nombre')->get();
        $this->seleccionados = $curso->estamentos->pluck('id')->map(fn ($id) => (int) $id)->toArray();
    }

    public function toggleEstamento(int $estamentoId): void
    {
        if (in_array($estamentoId, $this->seleccionados)) {
            $this->seleccionados = array_values(
                array_filter($this->seleccionados, fn ($id) => $id !== $estamentoId)
            );
        } else {
            $this->seleccionados[] = $estamentoId;
        }
    }

    public function guardar(): void
    {
        $this->curso->estamentos()->sync($this->seleccionados);
        session()->flash('success', 'Asignaciones guardadas correctamente.');
        $this->dispatch('estamentos-guardados');
    }

    public function render()
    {
        return view('livewire.capacitador.gestion-estamentos');
    }
}
