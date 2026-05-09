<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\User;
use App\Services\Analytics\LearningAnalyticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(LearningAnalyticsService $analyticsService): View
    {
        $stats = Cache::flexible('admin_dashboard_stats', [60, 300], function () {
            $currentYear = now()->year;
            $totalUsers = User::where('activo', true)->count();
            $usersConCertificadoEsteAnio = Certificado::whereYear('fecha_emision', $currentYear)
                ->distinct('user_id')->count('user_id');

            return [
                'totalUsers' => $totalUsers,
                'totalCursos' => Curso::count(),
                'totalCertificados' => Certificado::whereYear('fecha_emision', $currentYear)->count(),
                'cumplimientoAnual' => $totalUsers > 0
                    ? round(($usersConCertificadoEsteAnio / $totalUsers) * 100)
                    : 0,
            ];
        });

        $lmsStats = Cache::flexible('admin_dashboard_lms_stats_v2', [60, 300], function () use ($analyticsService): array {
            return $analyticsService->summaryFromAggregates();
        });

        return view('admin.dashboard', compact('stats', 'lmsStats'));
    }
}
