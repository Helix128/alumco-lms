<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Estamento;
use App\Models\Curso;
use Illuminate\Http\Request;
use App\Exports\ReporteExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    // 1. MÉTODO PARA MOSTRAR LA VISTA Y FILTRAR
    public function index(Request $request)
    {
        $estamentos = Estamento::all();
        $cursos = Curso::all();

        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        $query->when($request->filled('estamento_id'), function ($q) use ($request) {
            $q->where('estamento_id', $request->estamento_id);
        });

        if ($request->filled('curso_id') || ($request->filled('fecha_inicio') && $request->filled('fecha_fin'))) {
            $query->whereHas('certificados', function ($q) use ($request) {
                if ($request->filled('curso_id')) {
                    $q->where('curso_id', $request->curso_id);
                }
                if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                    $q->whereBetween('fecha_emision', [$request->fecha_inicio, $request->fecha_fin]);
                }
            });
        }

        $usuarios = $query->paginate(15)->withQueryString();

        return view('reportes.index', compact('usuarios', 'estamentos', 'cursos'));
    }

    // 2. MÉTODO PARA DESCARGAR EL EXCEL
    public function exportar(Request $request)
    {
        return Excel::download(new ReporteExport($request), 'reporte_capacitaciones.xlsx');
    }
}