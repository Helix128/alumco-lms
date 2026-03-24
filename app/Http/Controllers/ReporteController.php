<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    private function sanitizeEstamentoIds(Request $request): array
    {
        $rawIds = $request->input('estamento_id', []);
        if (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }

        $ids = array_map('intval', $rawIds);
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));

        return $ids;
    }

    private function sanitizeCourseIds(Request $request): array
    {
        $rawIds = $request->input('curso_id', []);
        if (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }

        $ids = array_map('intval', $rawIds);
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));

        return $ids;
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

    // Metodo para mostrar la vista y filtrar
    public function index(Request $request)
    {
        $estamentos = Estamento::all();
        $cursos = Curso::all();
        $selectedEstamentoIds = $this->sanitizeEstamentoIds($request);
        $selectedCourseIds = $this->sanitizeCourseIds($request);
        [$edadMin, $edadMax] = $this->sanitizeAgeRange($request);

        $ageBounds = ['min' => 0, 'max' => 120];
        $ageStats = User::query()
            ->whereNotNull('fecha_nacimiento')
            ->selectRaw('MIN(fecha_nacimiento) as min_birth, MAX(fecha_nacimiento) as max_birth')
            ->first();

        if ($ageStats && $ageStats->min_birth && $ageStats->max_birth) {
            $today = Carbon::today();
            $youngestAge = Carbon::parse($ageStats->max_birth)->diffInYears($today);
            $oldestAge = Carbon::parse($ageStats->min_birth)->diffInYears($today);

            $ageBounds['min'] = min($youngestAge, $oldestAge);
            $ageBounds['max'] = max($youngestAge, $oldestAge);
        }

        if ($edadMin !== null) {
            $edadMin = max($ageBounds['min'], min($edadMin, $ageBounds['max']));
        }

        if ($edadMax !== null) {
            $edadMax = max($ageBounds['min'], min($edadMax, $ageBounds['max']));
        }

        if ($edadMin !== null && $edadMax !== null && $edadMin > $edadMax) {
            [$edadMin, $edadMax] = [$edadMax, $edadMin];
        }

        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        if ($edadMin !== null || $edadMax !== null) {
            $query->whereNotNull('fecha_nacimiento');

            if ($edadMin !== null) {
                $query->where('fecha_nacimiento', '<=', now()->subYears($edadMin)->toDateString());
            }

            if ($edadMax !== null) {
                $query->where('fecha_nacimiento', '>=', now()->subYears($edadMax + 1)->addDay()->toDateString());
            }
        }

        if (!empty($selectedEstamentoIds)) {
            $query->whereIn('estamento_id', $selectedEstamentoIds);
        }

        if (!empty($selectedCourseIds) || ($request->filled('fecha_inicio') && $request->filled('fecha_fin'))) {
            $query->whereHas('certificados', function ($q) use ($request, $selectedCourseIds) {
                if (!empty($selectedCourseIds)) {
                    // AND logic: the user must have every selected course.
                    $q->whereIn('curso_id', $selectedCourseIds)
                        ->select('user_id')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(DISTINCT curso_id) = ?', [count($selectedCourseIds)]);
                }

                if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                    $q->whereBetween('fecha_emision', [$request->fecha_inicio, $request->fecha_fin]);
                }
            });
        }

        $usuarios = $query->paginate(15)->withQueryString();

        return view('reportes.index', compact('usuarios', 'estamentos', 'cursos', 'ageBounds'));
    }

    // Metodo para descargar el excel
    public function exportar(Request $request)
    {
        return Excel::download(new ReporteExport($request), 'reporte_capacitaciones.xlsx');
    }
}
