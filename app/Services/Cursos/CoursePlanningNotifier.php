<?php

namespace App\Services\Cursos;

use App\Models\Curso;
use App\Models\NotificationDelivery;
use App\Models\PlanificacionCurso;
use App\Notifications\CoursePlanningNotification;

class CoursePlanningNotifier
{
    public function notifyScheduled(PlanificacionCurso $planificacion): void
    {
        if (! $this->isFuturePlanning($planificacion)) {
            return;
        }

        $curso = $planificacion->curso;
        if (! $curso instanceof Curso) {
            return;
        }

        $this->notifyAudience($planificacion, $curso, NotificationDelivery::CoursePlanningScheduled, 'scheduled');
    }

    public function notifyUpdated(PlanificacionCurso $planificacion): void
    {
        if (! $this->isFuturePlanning($planificacion)) {
            return;
        }

        $curso = $planificacion->curso;
        if (! $curso instanceof Curso) {
            return;
        }

        $this->notifyAudience($planificacion, $curso, NotificationDelivery::CoursePlanningUpdated, 'updated');
    }

    private function notifyAudience(PlanificacionCurso $planificacion, Curso $curso, string $deliveryType, string $notificationType): void
    {
        $audience = app(CourseNotificationAudience::class);
        $workers = $audience->workersForPlanning($planificacion)->get();

        foreach ($workers as $worker) {
            $dedupeKey = $deliveryType === NotificationDelivery::CoursePlanningScheduled
                ? NotificationDelivery::planningScheduledKey($worker, $curso, $planificacion)
                : NotificationDelivery::planningUpdatedKey($worker, $curso, $planificacion);

            $recorded = NotificationDelivery::recordOnce($dedupeKey, [
                'user_id' => $worker->id,
                'curso_id' => $curso->id,
                'planificacion_curso_id' => $planificacion->id,
                'type' => $deliveryType,
            ]);

            if (! $recorded) {
                continue;
            }

            $worker->notify(new CoursePlanningNotification($curso, $planificacion, $notificationType));
        }
    }

    private function isFuturePlanning(PlanificacionCurso $planificacion): bool
    {
        return $planificacion->fecha_inicio->startOfDay()->gt(now()->startOfDay());
    }
}
