<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class EstadisticasController extends Controller
{
    public function index(): View
    {
        return view('capacitador.estadisticas', [
            'capacitadorId' => auth()->id(),
        ]);
    }
}
