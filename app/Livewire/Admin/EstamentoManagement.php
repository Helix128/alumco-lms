<?php

namespace App\Livewire\Admin;

use App\Models\Estamento;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class EstamentoManagement extends Component
{
    public string $nombre = '';

    public ?int $editingId = null;

    public bool $showForm = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAdminAccess(), 403);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $estamentoId): void
    {
        $estamento = Estamento::query()->findOrFail($estamentoId);

        $this->editingId = $estamento->id;
        $this->nombre = $estamento->nombre;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->nombre = $this->normalizeName($this->nombre);

        $this->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:120'],
        ], [
            'nombre.required' => 'El nombre del estamento es obligatorio.',
            'nombre.min' => 'El nombre del estamento debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre del estamento no puede superar 120 caracteres.',
        ]);

        $nombre = $this->nombre;
        $duplicate = Estamento::withTrashed()
            ->where('nombre', $nombre)
            ->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))
            ->first();

        if ($duplicate && ! $duplicate->trashed()) {
            $this->addError('nombre', 'Ya existe un estamento con ese nombre.');

            return;
        }

        if ($this->editingId) {
            $estamento = Estamento::query()->findOrFail($this->editingId);
            $estamento->update(['nombre' => $nombre]);

            session()->flash('success', 'Estamento actualizado correctamente.');
        } elseif ($duplicate?->trashed()) {
            $duplicate->restore();
            $duplicate->update(['nombre' => $nombre]);

            session()->flash('success', 'Estamento restaurado correctamente.');
        } else {
            Estamento::query()->create(['nombre' => $nombre]);

            session()->flash('success', 'Estamento creado correctamente.');
        }

        $this->resetForm();
        $this->dispatch('saved');
    }

    public function deleteEstamento(int $estamentoId): void
    {
        $estamento = Estamento::query()
            ->withCount(['users', 'cursos'])
            ->findOrFail($estamentoId);

        if ($estamento->users_count > 0 || $estamento->cursos_count > 0) {
            session()->flash('error', 'No se puede eliminar un estamento asociado a colaboradores o cursos.');

            return;
        }

        $estamento->delete();
        session()->flash('success', 'Estamento eliminado correctamente.');
        $this->resetForm();
        $this->dispatch('saved');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function render(): View
    {
        $estamentos = Estamento::query()
            ->withCount(['users', 'cursos'])
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.estamento-management', [
            'estamentos' => $estamentos,
        ]);
    }

    private function resetForm(): void
    {
        $this->nombre = '';
        $this->editingId = null;
        $this->showForm = false;
        $this->resetValidation();
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/\s+/', ' ', trim($name)) ?: '';
    }
}
