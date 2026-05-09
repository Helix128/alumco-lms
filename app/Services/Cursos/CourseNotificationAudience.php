<?php

namespace App\Services\Cursos;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CourseNotificationAudience
{
    /**
     * @return Builder<User>
     */
    public function workersForPlanning(PlanificacionCurso $planificacion): Builder
    {
        $curso = $planificacion->curso;

        if (! $curso instanceof Curso) {
            return User::query()->whereRaw('1 = 0');
        }

        $estamentoIds = $curso->estamentos->pluck('id');

        if ($estamentoIds->isEmpty()) {
            return User::query()->whereRaw('1 = 0');
        }

        return User::query()
            ->role('Trabajador')
            ->where('activo', true)
            ->whereIn('estamento_id', $estamentoIds)
            ->when($planificacion->sede_id, function (Builder $query) use ($planificacion): void {
                $query->where('sede_id', $planificacion->sede_id);
            });
    }
}
