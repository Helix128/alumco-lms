<?php

namespace App\Services\Calendario;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CalendarPlanningRepository
{
    public function __construct(
        private readonly CalendarCacheKeyService $cacheKeyService
    ) {}

    /**
     * @param  array<int, string>  $palette
     * @return array{todos: array<int, array{id: int, titulo: string, bg: string}>, planificados: array<int, int>}
     */
    public function preheatAvailableCourses(
        int $anio,
        int $mes,
        string $modoVista,
        User $user,
        array $palette
    ): array {
        $cacheKey = $this->cacheKeyService->make(
            'cursos_disponibles',
            [
                'vista' => $modoVista,
                'anio' => $anio,
                'mes' => $mes,
                'user' => $user->id,
                'admin' => (int) $user->hasAdminAccess(),
                'capacitador' => (int) $user->isCapacitador(),
            ]
        );

        return Cache::flexible(
            $cacheKey,
            [30, 120],
            function () use ($anio, $mes, $modoVista, $user, $palette): array {
                if ($modoVista === 'anual') {
                    $inicioAnio = Carbon::create($anio, 1, 1)->startOfDay();
                    $finAnio = Carbon::create($anio, 12, 31)->endOfDay();

                    $planificadosIds = PlanificacionCurso::where('fecha_inicio', '<=', $finAnio)
                        ->where('fecha_fin', '>=', $inicioAnio)
                        ->pluck('curso_id')
                        ->unique()
                        ->all();
                } else {
                    $primerDia = Carbon::createFromDate($anio, $mes, 1)->startOfDay();
                    $ultimoDia = $primerDia->copy()->endOfMonth()->startOfDay();

                    $planificadosIds = PlanificacionCurso::where('fecha_inicio', '<=', $ultimoDia)
                        ->where('fecha_fin', '>=', $primerDia)
                        ->pluck('curso_id')
                        ->unique()
                        ->all();
                }

                $query = Curso::query()->orderBy('titulo');

                if ($user->isCapacitador() && ! $user->hasAdminAccess()) {
                    $query->where('capacitador_id', $user->id);
                }

                $todos = $query
                    ->get(['id', 'titulo'])
                    ->map(fn ($curso) => [
                        'id' => $curso->id,
                        'titulo' => $curso->titulo,
                        'bg' => $palette[$curso->id % count($palette)],
                    ])
                    ->all();

                return [
                    'todos' => $todos,
                    'planificados' => $planificadosIds,
                ];
            }
        );
    }

    /**
     * @param  array<int, int>  $filtroSedesIds
     * @return Collection<int, PlanificacionCurso>
     */
    public function getPlanificaciones(Carbon $desde, Carbon $hasta, User $user, array $filtroSedesIds): Collection
    {
        $cacheKey = $this->cacheKeyService->make(
            'planificaciones_ids',
            [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
                'user' => $user->id,
                'admin' => (int) $user->hasAdminAccess(),
                'capacitador' => (int) $user->isCapacitador(),
                'estamento' => $user->estamento_id,
                'sede' => $user->sede_id,
                'filtro_sedes' => implode(',', $filtroSedesIds),
            ]
        );

        $planificacionIds = Cache::flexible($cacheKey, [30, 120], function () use ($desde, $hasta, $user, $filtroSedesIds): array {
            $query = PlanificacionCurso::query()
                ->where('fecha_inicio', '<=', $hasta)
                ->where('fecha_fin', '>=', $desde);

            if ($user->isCapacitador() && ! $user->hasAdminAccess()) {
                $query->whereHas('curso', fn ($subquery) => $subquery->where('capacitador_id', $user->id));
            } elseif (! $user->hasAdminAccess()) {
                $query->whereHas(
                    'curso.estamentos',
                    fn ($subquery) => $subquery->where('estamentos.id', $user->estamento_id)
                );

                if ($user->sede_id) {
                    $query->where(fn ($subquery) => $subquery->whereNull('sede_id')->orWhere('sede_id', $user->sede_id));
                }
            }

            if (! empty($filtroSedesIds)) {
                $query->where(fn ($subquery) => $subquery->whereNull('sede_id')->orWhereIn('sede_id', $filtroSedesIds));
            }

            return $query->orderBy('fecha_inicio')->pluck('id')->all();
        });

        if (empty($planificacionIds)) {
            return collect();
        }

        return PlanificacionCurso::query()
            ->with('curso:id,titulo,capacitador_id', 'sede:id,nombre')
            ->whereIn('id', $planificacionIds)
            ->orderBy('fecha_inicio')
            ->get();
    }

    public function yearHasPlanificaciones(int $anio): bool
    {
        $inicio = Carbon::create($anio, 1, 1)->startOfDay();
        $fin = Carbon::create($anio, 12, 31)->endOfDay();

        return PlanificacionCurso::where('fecha_inicio', '<=', $fin)
            ->where('fecha_fin', '>=', $inicio)
            ->exists();
    }
}
