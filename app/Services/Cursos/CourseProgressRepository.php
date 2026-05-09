<?php

namespace App\Services\Cursos;

use App\Models\Curso;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseProgressRepository
{
    /**
     * @param  Collection<int, int>  $workerIds
     * @return array<int, int>
     */
    public function percentagesForWorkers(Curso $curso, Collection $workerIds): array
    {
        $workerIds = $workerIds
            ->map(fn ($workerId): int => (int) $workerId)
            ->filter(fn (int $workerId): bool => $workerId > 0)
            ->unique()
            ->values();

        if ($workerIds->isEmpty()) {
            return [];
        }

        $totalModules = $curso->modulos()->count();

        if ($totalModules === 0) {
            return $workerIds->mapWithKeys(fn (int $workerId): array => [$workerId => 0])->all();
        }

        $completedByWorker = DB::table('progresos_modulo')
            ->join('modulos', 'modulos.id', '=', 'progresos_modulo.modulo_id')
            ->where('modulos.curso_id', $curso->id)
            ->whereIn('progresos_modulo.user_id', $workerIds)
            ->where('progresos_modulo.completado', true)
            ->selectRaw('progresos_modulo.user_id, COUNT(DISTINCT modulos.id) as completed_modules')
            ->groupBy('progresos_modulo.user_id')
            ->pluck('completed_modules', 'user_id');

        return $workerIds
            ->mapWithKeys(function (int $workerId) use ($completedByWorker, $totalModules): array {
                $completedModules = (int) $completedByWorker->get($workerId, 0);

                return [$workerId => (int) round(($completedModules / $totalModules) * 100)];
            })
            ->all();
    }
}
