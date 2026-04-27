<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    private function sanitizeSedeIds(Request $request): array
    {
        $rawIds = $request->input('sede_id', []);
        if (is_string($rawIds) && str_contains($rawIds, ',')) {
            $rawIds = explode(',', $rawIds);
        } elseif (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }
        return array_values(array_unique(array_filter(array_map('intval', $rawIds), fn($id) => $id > 0)));
    }

    private function sanitizeEstamentoIds(Request $request): array
    {
        $rawIds = $request->input('estamento_id', []);
        if (is_string($rawIds) && str_contains($rawIds, ',')) {
            $rawIds = explode(',', $rawIds);
        } elseif (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }
        return array_values(array_unique(array_filter(array_map('intval', $rawIds), fn($id) => $id > 0)));
    }

    private function sanitizeCourseIds(Request $request): array
    {
        $rawIds = $request->input('curso_id', []);
        if (is_string($rawIds) && str_contains($rawIds, ',')) {
            $rawIds = explode(',', $rawIds);
        } elseif (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }
        return array_values(array_unique(array_filter(array_map('intval', $rawIds), fn($id) => $id > 0)));
    }

    public function index(Request $request)
    {
        $estamentos = Estamento::all();
        $cursos = Curso::all();
        $sedes = Sede::all();
        $cursoSeleccionado = null;

        // Variables de estado para los filtros (necesarias para la vista)
        $selectedSedes = $this->sanitizeSedeIds($request);
        $selectedEstamentos = $this->sanitizeEstamentoIds($request);
        $selectedCursos = $this->sanitizeCourseIds($request);
        
        $edadMinReq = $request->input('edad_min');
        $edadMaxReq = $request->input('edad_max');

        // Límites para el slider de edad
        $minDateLimit = Carbon::now()->subYears(100)->format('Y-m-d');
        $maxDateLimit = Carbon::now()->format('Y-m-d');

        $maxNac = User::whereNotNull('fecha_nacimiento')->whereBetween('fecha_nacimiento', [$minDateLimit, $maxDateLimit])->max('fecha_nacimiento');
        $minNac = User::whereNotNull('fecha_nacimiento')->whereBetween('fecha_nacimiento', [$minDateLimit, $maxDateLimit])->min('fecha_nacimiento');

        $ageBounds = [
            'min' => $maxNac ? Carbon::parse($maxNac)->age : 18,
            'max' => $minNac ? Carbon::parse($minNac)->age : 80,
        ];

        // El filtro de edad ahora siempre se considera activo y usa los límites de la BD por defecto
        $edadActiva = true;
        $edadMin = is_numeric($edadMinReq) ? (int)$edadMinReq : $ageBounds['min'];
        $edadMax = is_numeric($edadMaxReq) ? (int)$edadMaxReq : $ageBounds['max'];

        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        // 1. Filtro por Estamento
        if (!empty($selectedEstamentos)) {
            $query->whereIn('estamento_id', $selectedEstamentos);
        }

        // 2. Filtro por Sede 
        if (!empty($selectedSedes)) {
            $query->whereIn('sede_id', $selectedSedes);
        }

        // 3. Filtro por Curso
        if (!empty($selectedCursos)) {
            foreach ($selectedCursos as $id) {
                $query->whereHas('certificados', function ($q) use ($id) {
                    $q->where('curso_id', $id);
                });
            }

            if (count($selectedCursos) === 1) {
                $cursoSeleccionado = Curso::withCount('modulos')->find($selectedCursos[0]);
                if ($cursoSeleccionado) {
                    $query->withCount([
                        'progresos as modulos_completados_count' => function ($q) use ($cursoSeleccionado) {
                            $q->whereHas('modulo', function ($q2) use ($cursoSeleccionado) {
                                $q2->where('curso_id', $cursoSeleccionado->id);
                            })->where('completado', true);
                        }
                    ]);
                }
            } else {
                $cursoSeleccionado = Curso::withCount('modulos')->find($selectedCursos[0]);
            }
        }

        // 4. Filtro por Edad (siempre aplicado, usando defaults si es necesario)
        $query->where('fecha_nacimiento', '<=', Carbon::now()->subYears($edadMin)->format('Y-m-d'));
        $query->where('fecha_nacimiento', '>=', Carbon::now()->subYears($edadMax + 1)->addDay()->format('Y-m-d'));

        // 5. Filtro por Fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereHas('certificados', function ($q) use ($request) {
                $q->whereBetween('fecha_emision', [$request->fecha_inicio, $request->fecha_fin]);
                $cursoIds = $this->sanitizeCourseIds($request);
                if (!empty($cursoIds)) {
                    $q->whereIn('curso_id', $cursoIds);
                }
            });
        }

        $usuarios = $query->paginate(15)->withQueryString();

        return view('admin.reportes.index', compact(
            'usuarios', 'estamentos', 'cursos', 'sedes', 
            'cursoSeleccionado', 'ageBounds',
            'selectedSedes', 'selectedEstamentos', 'selectedCursos', 'edadActiva'
        ));
    }

    public function exportar(Request $request)
    {
        return Excel::download(new ReporteExport($request), 'reporte_capacitaciones.xlsx');
    }
}
