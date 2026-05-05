<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class GaugeAnualCapacitador extends Component
{
    public int $porcentaje = 0;

    public function render()
    {
        return view('livewire.admin.gauge-anual-capacitador');
    }
}
