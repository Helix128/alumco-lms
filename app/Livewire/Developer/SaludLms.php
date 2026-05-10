<?php

namespace App\Livewire\Developer;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class SaludLms extends Component
{
    public string $view = 'resumen';

    public function mount(): void
    {
        Gate::authorize('viewLmsHealth');

        $this->view = $this->normalizeView((string) request()->query('vista', 'resumen'));
    }

    public function refresh(): void
    {
        Gate::authorize('viewLmsHealth');

        $this->dispatch('refreshed');
    }

    private function normalizeView(string $view): string
    {
        return in_array($view, ['resumen', 'jobs', 'logs', 'datos'], true) ? $view : 'resumen';
    }

    public function render(): View
    {
        Gate::authorize('viewLmsHealth');

        $this->view = $this->normalizeView($this->view);

        return view('livewire.developer.salud-lms')
            ->extends('layouts.panel')
            ->section('content');
    }
}
