<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Http\Requests\ReportFilterRequest;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    private function sanitizeIdFilter(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
    }

    public function index(ReportFilterRequest $request)
    {
        $data = $request->validated();
        $estamentos = Estamento::all();
        $cursos = Curso::all();
        $sedes = Sede::all();
        $cursoSeleccionado = null;

        $selectedSedes = $this->sanitizeIdFilter($data['sede_id'] ?? []);
        $selectedEstamentos = $this->sanitizeIdFilter($data['estamento_id'] ?? []);
        $selectedCursos = $this->sanitizeIdFilter($data['curso_id'] ?? []);

        $edadMinReq = $data['edad_min'] ?? null;
        $edadMaxReq = $data['edad_max'] ?? null;

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
        $edadMin = is_numeric($edadMinReq) ? (int) $edadMinReq : $ageBounds['min'];
        $edadMax = is_numeric($edadMaxReq) ? (int) $edadMaxReq : $ageBounds['max'];

        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        // 1. Filtro por Estamento
        if (! empty($selectedEstamentos)) {
            $query->whereIn('estamento_id', $selectedEstamentos);
        }

        // 2. Filtro por Sede
        if (! empty($selectedSedes)) {
            $query->whereIn('sede_id', $selectedSedes);
        }

        // 3. Filtro por Curso
        if (! empty($selectedCursos)) {
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
                        },
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
        if (! empty($data['fecha_inicio']) && ! empty($data['fecha_fin'])) {
            $query->whereHas('certificados', function ($q) use ($data, $selectedCursos) {
                $q->whereBetween('fecha_emision', [$data['fecha_inicio'], $data['fecha_fin']]);
                if (! empty($selectedCursos)) {
                    $q->whereIn('curso_id', $selectedCursos);
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

    public function exportar(ReportFilterRequest $request)
    {
        return Excel::download(new ReporteExport($request), 'reporte_capacitaciones.xlsx');
    }
}
