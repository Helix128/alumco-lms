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
    // --- FUNCIONES DE TU AMIGO (Ligeramente mejoradas para soportar comas) ---
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

    private function sanitizeAgeRange(Request $request): array
    {
        $edadMin = $request->input('edad_min');
        $edadMax = $request->input('edad_max');

        $edadMin = is_numeric($edadMin) ? max((int) $edadMin, 0) : null;
        $edadMax = is_numeric($edadMax) ? max((int) $edadMax, 0) : null;

        if ($edadMin !== null && $edadMax !== null && $edadMin > $edadMax) {
            [$edadMin, $edadMax] = [$edadMax, $edadMin];
        }

        return [$edadMin, $edadMax];
    }

    // --- MÉTODO PRINCIPAL ---
    public function index(Request $request)
    {
        $estamentos = Estamento::all();
        $cursos = Curso::all();
        $sedes = Sede::all();
        $cursoSeleccionado = null;

        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        // 1. Filtro por Estamento
        $estamentoIds = $this->sanitizeEstamentoIds($request);
        if (!empty($estamentoIds)) {
            $query->whereIn('estamento_id', $estamentoIds);
        }

        // 2. Filtro por Sede 
        $sedeIds = $this->sanitizeSedeIds($request);
        if (!empty($sedeIds)) {
            $query->whereIn('sede_id', $sedeIds);
        }

        // 3. Filtro por Curso (AHORA SÍ USA LA FUNCIÓN DE TU AMIGO)
        $cursoIds = $this->sanitizeCourseIds($request);
        if (!empty($cursoIds)) {
            $query->whereHas('progresos.modulo', function ($q) use ($cursoIds) {
                $q->whereIn('curso_id', $cursoIds);
            });

            $cursoSeleccionado = Curso::withCount('modulos')->find($cursoIds[0]);

            if ($cursoSeleccionado) {
                $query->withCount([
                    'progresos as modulos_completados_count' => function ($q) use ($cursoSeleccionado) {
                        $q->whereHas('modulo', function ($q2) use ($cursoSeleccionado) {
                            $q2->where('curso_id', $cursoSeleccionado->id);
                        })->where('completado', true);
                    }
                ]);
            }
        }

        // 4. Filtro por Fechas
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

        $minDate = User::whereNotNull('fecha_nacimiento')->max('fecha_nacimiento');
        $maxDate = User::whereNotNull('fecha_nacimiento')->min('fecha_nacimiento');

        $ageBounds = [
            'min' => $minDate ? Carbon::parse($minDate)->age : 0,
            'max' => $maxDate ? Carbon::parse($maxDate)->age : 120,
        ];

        // Asegurarse de que min <= max en caso de que minDate de un salto raro
        if ($ageBounds['min'] > $ageBounds['max']) {
            $tmp = $ageBounds['min'];
            $ageBounds['min'] = $ageBounds['max'];
            $ageBounds['max'] = $tmp;
        }

        return view('admin.reportes.index', compact('usuarios', 'estamentos', 'cursos', 'sedes', 'cursoSeleccionado', 'ageBounds'));
    }

    // Metodo para descargar el excel
    public function exportar(Request $request)
    {
        return Excel::download(new ReporteExport($request), 'reporte_capacitaciones.xlsx');
    }
}