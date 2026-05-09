<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Feedback;
use App\Models\ProgresoModulo;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Services\Analytics\LearningAnalyticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(LearningAnalyticsService $analyticsService): View
    {
        ['stats' => $stats, 'lmsStats' => $lmsStats] = Cache::flexible('admin_dashboard_stats', [60, 300], function () {
            $currentYear = now()->year;
            $totalUsers = User::where('activo', true)->count();
            $usersConCertificadoEsteAnio = Certificado::whereYear('fecha_emision', $currentYear)
                ->distinct('user_id')->count('user_id');
            $courseIds = Curso::query()->pluck('id');

            return [
                'stats' => [
                    'totalUsers' => $totalUsers,
                    'totalCursos' => $courseIds->count(),
                    'totalCertificados' => Certificado::whereYear('fecha_emision', $currentYear)->count(),
                    'cumplimientoAnual' => $totalUsers > 0
                        ? round(($usersConCertificadoEsteAnio / $totalUsers) * 100)
                        : 0,
                ],
                'lmsStats' => $this->buildLearningStats($courseIds),
            ];
        });

        return view('admin.dashboard', compact('stats', 'lmsStats'));
    }

    private function buildLearningStats(Collection $courseIds): array
    {
        if ($courseIds->isEmpty()) {
            return [
                'total_participantes' => 0,
                'iniciados' => 0,
                'completados' => 0,
                'en_riesgo' => 0,
                'feedback_promedio' => null,
            ];
        }

        $totalParticipantes = ProgresoModulo::query()
            ->whereHas('modulo', fn ($query) => $query->whereIn('curso_id', $courseIds))
            ->distinct('user_id')
            ->count('user_id');

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

        $feedbackPromedio = Feedback::query()
            ->whereIn('curso_id', $courseIds)
            ->whereNotNull('rating')
            ->avg('rating');

        return [
            'total_participantes' => $totalParticipantes,
            'iniciados' => $iniciados,
            'completados' => $completados,
            'en_riesgo' => max($iniciados - $completados, 0),
            'feedback_promedio' => $feedbackPromedio !== null ? number_format((float) $feedbackPromedio, 1) : null,
        ];
        $lmsStats = Cache::flexible('admin_dashboard_lms_stats_v2', [60, 300], function () use ($analyticsService): array {
            return $analyticsService->summaryFromAggregates();
        });

        return view('admin.dashboard', compact('stats', 'lmsStats'));
    }
}
