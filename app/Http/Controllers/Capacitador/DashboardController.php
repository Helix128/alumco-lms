<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\ProgresoModulo;
use Illuminate\Support\Collection;
use App\Services\Analytics\LearningAnalyticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(LearningAnalyticsService $analyticsService): View
    {
        $user = auth()->user();
        $cacheScope = $user->hasAdminAccess() ? 'admin' : "capacitador_{$user->id}";
        $cacheKey = "dashboard_summary_v2_{$cacheScope}";

        ['stats' => $stats, 'ultimosCursos' => $ultimosCursos, 'learningStats' => $learningStats] = Cache::flexible(
            $cacheKey,
            [30, 120],
            function () use ($user, $analyticsService): array {
                $cursosQuery = $user->hasAdminAccess()
                    ? Curso::query()
                    : $user->cursosImpartidos();

                $cursos = $cursosQuery
                    ->withCount(['modulos', 'estamentos', 'planificaciones'])
                    ->orderByDesc('created_at')
                    ->get();

                $cursoIds = $cursos->pluck('id');
                $totalParticipantes = $cursoIds->isEmpty()
                    ? 0
                    : ProgresoModulo::whereHas('modulo', fn ($query) => $query->whereIn('curso_id', $cursoIds))
                        ->distinct('user_id')
                        ->count('user_id');

                $totalCertificados = $cursoIds->isEmpty()
                    ? 0
                    : Certificado::whereIn('curso_id', $cursoIds)->count();
                $learningStats = $analyticsService->summaryForCourseIds($cursoIds);

                return [
                    'stats' => [
                        'cursos' => $cursos->count(),
                        'participantes' => $totalParticipantes,
                        'certificados' => $totalCertificados,
                    ],
                    'ultimosCursos' => $cursos
                        ->take(5)
                        ->map(fn (Curso $curso): array => [
                            'id' => $curso->id,
                            'titulo' => $curso->titulo,
                            'modulos_count' => $curso->modulos_count,
                            'planificaciones_count' => $curso->planificaciones_count,
                        ])
                        ->values()
                        ->all(),
                    'learningStats' => $this->buildLearningStats($cursoIds),
                    'learningStats' => $learningStats,
                ];
            }
        );

        return view('capacitador.dashboard', compact('stats', 'ultimosCursos', 'learningStats'));
    }

    private function buildLearningStats(Collection $courseIds): array
    {
        if ($courseIds->isEmpty()) {
            return [
                'iniciados' => 0,
                'completados' => 0,
                'en_riesgo' => 0,
            ];
        }

        $moduleTotals = DB::table('modulos')
            ->whereIn('curso_id', $courseIds)
            ->select('curso_id', DB::raw('COUNT(*) as total_modulos'))
            ->groupBy('curso_id')
            ->pluck('total_modulos', 'curso_id');

        $progressByCourseAndUser = DB::table('progresos_modulo as progreso')
            ->join('modulos', 'modulos.id', '=', 'progreso.modulo_id')
            ->whereIn('modulos.curso_id', $courseIds)
            ->selectRaw('modulos.curso_id, progreso.user_id, COUNT(DISTINCT progreso.modulo_id) as modulos_tocados')
            ->selectRaw('COUNT(DISTINCT CASE WHEN progreso.completado = 1 THEN progreso.modulo_id END) as modulos_completados')
            ->groupBy('modulos.curso_id', 'progreso.user_id')
            ->get();

        $iniciados = $progressByCourseAndUser
            ->where('modulos_tocados', '>', 0)
            ->pluck('user_id')
            ->unique()
            ->count();

        $completados = $progressByCourseAndUser
            ->filter(function ($row) use ($moduleTotals): bool {
                $totalModulosCurso = (int) ($moduleTotals[$row->curso_id] ?? 0);

                return $totalModulosCurso > 0 && (int) $row->modulos_completados >= $totalModulosCurso;
            })
            ->pluck('user_id')
            ->unique()
            ->count();

        return [
            'iniciados' => $iniciados,
            'completados' => $completados,
            'en_riesgo' => max($iniciados - $completados, 0),
        ];
    }
}
