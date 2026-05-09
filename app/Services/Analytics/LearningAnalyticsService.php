<?php

namespace App\Services\Analytics;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Feedback;
use App\Models\IntentoEvaluacion;
use App\Models\ProgresoModulo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LearningAnalyticsService
{
    /**
     * @param  EloquentCollection<int, Curso>|Collection<int, Curso>  $cursos
     * @return array{total_participantes: int, iniciados: int, completados: int, en_riesgo: int, feedback_promedio: float|null}
     */
    public function summaryForCourses(EloquentCollection|Collection $cursos): array
    {
        $courseIds = $cursos->pluck('id')->values();

        if ($courseIds->isEmpty()) {
            return [
                'total_participantes' => 0,
                'iniciados' => 0,
                'completados' => 0,
                'en_riesgo' => 0,
                'feedback_promedio' => null,
            ];
        }

        $participants = $this->participantsForCourses($cursos);
        $progressByCourseUser = $this->progressPercentages($courseIds);
        $completedPairs = Certificado::query()
            ->whereIn('curso_id', $courseIds)
            ->get(['curso_id', 'user_id'])
            ->map(fn (Certificado $certificado): string => "{$certificado->curso_id}:{$certificado->user_id}")
            ->flip();

        $started = 0;
        $completed = 0;
        $atRisk = 0;
        $today = now()->startOfDay();

        foreach ($participants as $participant) {
            $key = "{$participant['curso_id']}:{$participant['user_id']}";
            $progress = $progressByCourseUser->get($key, 0);

            if ($progress > 0) {
                $started++;
            }

            if ($progress >= 100 || $completedPairs->has($key)) {
                $completed++;
            }

            if ($participant['fecha_fin'] && $participant['fecha_fin']->lte($today->copy()->addDays(7)) && $progress < 50) {
                $atRisk++;
            }
        }

        $feedbackAverage = Feedback::query()
            ->whereIn('curso_id', $courseIds)
            ->where('tipo', Feedback::TipoCurso)
            ->whereNotNull('rating')
            ->avg('rating');

        return [
            'total_participantes' => $participants->count(),
            'iniciados' => $started,
            'completados' => $completed,
            'en_riesgo' => $atRisk,
            'feedback_promedio' => $feedbackAverage !== null ? round((float) $feedbackAverage, 1) : null,
        ];
    }

    /**
     * @return array{total_participantes: int, iniciados: int, completados: int, en_riesgo: int, feedback_promedio: float|null}
     */
    public function summaryFromAggregates(): array
    {
        $courseIds = Curso::query()->pluck('id');

        if ($courseIds->isEmpty()) {
            return [
                'total_participantes' => 0,
                'iniciados' => 0,
                'completados' => 0,
                'en_riesgo' => 0,
                'feedback_promedio' => null,
            ];
        }

        $participants = DB::table('curso_estamento')
            ->join('users', 'users.estamento_id', '=', 'curso_estamento.estamento_id')
            ->leftJoin('planificaciones_cursos', 'planificaciones_cursos.curso_id', '=', 'curso_estamento.curso_id')
            ->whereIn('curso_estamento.curso_id', $courseIds)
            ->selectRaw('curso_estamento.curso_id, users.id as user_id, MAX(planificaciones_cursos.fecha_fin) as fecha_fin')
            ->groupBy('curso_estamento.curso_id', 'users.id')
            ->get();

        $progressByCourseUser = $this->progressPercentages($courseIds);
        $completedPairs = Certificado::query()
            ->whereIn('curso_id', $courseIds)
            ->get(['curso_id', 'user_id'])
            ->map(fn (Certificado $certificado): string => "{$certificado->curso_id}:{$certificado->user_id}")
            ->flip();

        $started = 0;
        $completed = 0;
        $atRisk = 0;
        $today = now()->startOfDay();

        foreach ($participants as $participant) {
            $key = "{$participant->curso_id}:{$participant->user_id}";
            $progress = $progressByCourseUser->get($key, 0);

            if ($progress > 0) {
                $started++;
            }

            if ($progress >= 100 || $completedPairs->has($key)) {
                $completed++;
            }

            $endsAt = $participant->fecha_fin ? Carbon::parse($participant->fecha_fin) : null;
            if ($endsAt?->lte($today->copy()->addDays(7)) && $progress < 50) {
                $atRisk++;
            }
        }

        $feedbackAverage = Feedback::query()
            ->whereIn('curso_id', $courseIds)
            ->where('tipo', Feedback::TipoCurso)
            ->whereNotNull('rating')
            ->avg('rating');

        return [
            'total_participantes' => $participants->count(),
            'iniciados' => $started,
            'completados' => $completed,
            'en_riesgo' => $atRisk,
            'feedback_promedio' => $feedbackAverage !== null ? round((float) $feedbackAverage, 1) : null,
        ];
    }

    /**
     * @return array{participantes: int, iniciados: int, completados: int, en_riesgo: int, intentos_fallidos: int, feedback_promedio: float|null, modulo_friccion: string|null}
     */
    public function courseSummary(Curso $curso): array
    {
        $summary = $this->summaryForCourses(collect([$curso]));
        $moduleIds = $curso->modulos()->pluck('id');

        $failedAttempts = IntentoEvaluacion::query()
            ->whereHas('evaluacion.modulo', fn ($query) => $query->where('curso_id', $curso->id))
            ->where('aprobado', false)
            ->count();

        $dropoff = ProgresoModulo::query()
            ->join('modulos', 'modulos.id', '=', 'progresos_modulo.modulo_id')
            ->whereIn('progresos_modulo.modulo_id', $moduleIds)
            ->where('progresos_modulo.completado', true)
            ->selectRaw('modulos.titulo, modulos.orden, COUNT(*) as completados')
            ->groupBy('modulos.id', 'modulos.titulo', 'modulos.orden')
            ->orderBy('completados')
            ->orderBy('modulos.orden')
            ->first();

        return [
            'participantes' => $summary['total_participantes'],
            'iniciados' => $summary['iniciados'],
            'completados' => $summary['completados'],
            'en_riesgo' => $summary['en_riesgo'],
            'intentos_fallidos' => $failedAttempts,
            'feedback_promedio' => $summary['feedback_promedio'],
            'modulo_friccion' => $dropoff?->titulo,
        ];
    }

    /**
     * @param  EloquentCollection<int, Curso>|Collection<int, Curso>  $cursos
     * @return Collection<int, array{curso_id: int, user_id: int, fecha_fin: Carbon|null}>
     */
    private function participantsForCourses(EloquentCollection|Collection $cursos): Collection
    {
        return $cursos->flatMap(function (Curso $curso): Collection {
            $curso->loadMissing(['estamentos.users', 'planificaciones']);
            $latestEnd = $curso->planificaciones->max('fecha_fin');

            return $curso->estamentos
                ->flatMap(fn ($estamento) => $estamento->users)
                ->unique('id')
                ->map(fn (User $user): array => [
                    'curso_id' => $curso->id,
                    'user_id' => $user->id,
                    'fecha_fin' => $latestEnd,
                ]);
        })->values();
    }

    /**
     * @param  Collection<int, int>  $courseIds
     * @return Collection<string, int>
     */
    private function progressPercentages(Collection $courseIds): Collection
    {
        $moduleCounts = DB::table('modulos')
            ->whereIn('curso_id', $courseIds)
            ->selectRaw('curso_id, COUNT(*) as total')
            ->groupBy('curso_id')
            ->pluck('total', 'curso_id');

        return DB::table('progresos_modulo')
            ->join('modulos', 'modulos.id', '=', 'progresos_modulo.modulo_id')
            ->whereIn('modulos.curso_id', $courseIds)
            ->where('progresos_modulo.completado', true)
            ->selectRaw('modulos.curso_id, progresos_modulo.user_id, COUNT(DISTINCT modulos.id) as completados')
            ->groupBy('modulos.curso_id', 'progresos_modulo.user_id')
            ->get()
            ->mapWithKeys(function (object $row) use ($moduleCounts): array {
                $total = (int) $moduleCounts->get($row->curso_id, 0);
                $progress = $total > 0 ? (int) round((((int) $row->completados) / $total) * 100) : 0;

                return ["{$row->curso_id}:{$row->user_id}" => $progress];
            });
    }
}
