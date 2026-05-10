<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\NotificationDelivery;
use App\Models\PlanificacionCurso;
use App\Models\SystemTaskRun;
use App\Notifications\CourseAvailableNotification;
use App\Services\Cursos\CourseNotificationAudience;
use App\Services\Cursos\CourseProgressRepository;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('lms:send-course-available-notifications')]
#[Description('Envía correos a trabajadores cuando un curso queda disponible.')]
class SendCourseAvailableNotifications extends Command
{
    public function handle(CourseNotificationAudience $audience, CourseProgressRepository $courseProgressRepository): int
    {
        $taskRun = SystemTaskRun::start('lms:send-course-available-notifications');
        $sent = 0;

        try {
            $today = now()->startOfDay();

            PlanificacionCurso::query()
                ->with(['curso.estamentos', 'curso.modulos'])
                ->where('fecha_inicio', '<=', $today)
                ->where('fecha_fin', '>=', $today)
                ->chunkById(50, function ($planificaciones) use ($audience, $courseProgressRepository, &$sent): void {
                    foreach ($planificaciones as $planificacion) {
                        $sent += $this->notifyWorkersFor($planificacion, $audience, $courseProgressRepository);
                    }
                });

            $taskRun->markSuccess($sent, "Notificaciones de cursos disponibles enviadas: {$sent}");
            $this->info("Notificaciones de cursos disponibles enviadas: {$sent}");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $taskRun->markFailed($exception);

            throw $exception;
        }
    }

    private function notifyWorkersFor(
        PlanificacionCurso $planificacion,
        CourseNotificationAudience $audience,
        CourseProgressRepository $courseProgressRepository
    ): int {
        $curso = $planificacion->curso;

        if (! $curso instanceof Curso) {
            return 0;
        }

        $sent = 0;

        $audience
            ->workersForPlanning($planificacion)
            ->chunkById(100, function ($workers) use ($curso, $planificacion, $courseProgressRepository, &$sent): void {
                $progressByWorker = $courseProgressRepository->percentagesForWorkers($curso, $workers->pluck('id'));

                foreach ($workers as $worker) {
                    if (($progressByWorker[$worker->id] ?? 0) === 100) {
                        continue;
                    }

                    $dedupeKey = NotificationDelivery::courseAvailableKey($worker, $curso, $planificacion);
                    $recorded = NotificationDelivery::recordOnce($dedupeKey, [
                        'user_id' => $worker->id,
                        'curso_id' => $curso->id,
                        'planificacion_curso_id' => $planificacion->id,
                        'type' => NotificationDelivery::CourseAvailable,
                    ]);

                    if (! $recorded) {
                        continue;
                    }

                    $worker->notify(new CourseAvailableNotification($curso, $planificacion));
                    $sent++;
                }
            });

        return $sent;
    }
}
