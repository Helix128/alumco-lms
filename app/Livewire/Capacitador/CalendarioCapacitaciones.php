<?php

namespace App\Livewire\Capacitador;

use Livewire\Component;
use Carbon\Carbon;

class CalendarioCapacitaciones extends Component
{
    public $mesActual;
    public $anioActual;
    public $mostrarModal = false;
    public $diaSeleccionado = null;
    public $tituloNuevo = '';
    public $tipoNuevo = 'taller';
    public $horaNueva = '10:00';
    public $filtroTipo = 'todos';
    public $evaluaciones = [];

    public function mount()
    {
        $this->mesActual = Carbon::now()->month;
        $this->anioActual = Carbon::now()->year;

        // Datos iniciales de prueba
        $this->evaluaciones = [
            ['id' => uniqid(), 'dia' => 14, 'mes' => 4, 'anio' => 2026, 'titulo' => 'Reunión Docente', 'tipo' => 'control'],
            ['id' => uniqid(), 'dia' => 18, 'mes' => 4, 'anio' => 2026, 'titulo' => 'Examen Práctico', 'tipo' => 'examen'],
        ];
    }

    public function abrirModal($dia)
    {
        $this->diaSeleccionado = $dia;
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->reset(['tituloNuevo', 'horaNueva', 'diaSeleccionado']);
    }

    public function guardarEvaluacion()
    {
        if(empty($this->tituloNuevo)) return;

        // Agregamos un ID único para poder borrarlo después sin errores
        $nuevaEv = [
            'id' => uniqid(),
            'dia' => $this->diaSeleccionado,
            'mes' => $this->mesActual,
            'anio' => $this->anioActual,
            'titulo' => $this->horaNueva . ' - ' . $this->tituloNuevo,
            'tipo' => $this->tipoNuevo
        ];

        $this->evaluaciones[] = $nuevaEv;
        
        // Forzamos la actualización del estado
        $this->evaluaciones = $this->evaluaciones; 

        $this->cerrarModal();
    }

    // --- NUEVA FUNCIÓN PARA BORRAR ---
    public function borrarEvaluacion($id)
    {
        $this->evaluaciones = array_filter($this->evaluaciones, function($ev) use ($id) {
            return $ev['id'] !== $id;
        });
    }

    public function mesAnterior() { /* tu lógica actual */ $this->mesActual--; if($this->mesActual < 1){ $this->mesActual=12; $this->anioActual--; } }
    public function mesSiguiente() { /* tu lógica actual */ $this->mesActual++; if($this->mesActual > 12){ $this->mesActual=1; $this->anioActual++; } }

    public function render()
    {
        return view('livewire.capacitador.calendario-capacitaciones')
               ->extends('layouts.panel') // Recuerda poner el nombre real de tu layout aquí
               ->section('content');
    }
}