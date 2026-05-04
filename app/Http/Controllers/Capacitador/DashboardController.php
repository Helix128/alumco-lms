<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\ProgresoModulo;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $cacheScope = $user->hasAdminAccess() ? 'admin' : "capacitador_{$user->id}";
        $cacheKey = "dashboard_summary_v2_{$cacheScope}";

        ['stats' => $stats, 'ultimosCursos' => $ultimosCursos] = Cache::flexible(
            $cacheKey,
            [30, 120],
            function () use ($user): array {
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
                ];
            }
        );

        return view('capacitador.dashboard', compact('stats', 'ultimosCursos'));
    }
}
