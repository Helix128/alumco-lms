<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\NotificationDelivery;
use App\Models\PlanificacionCurso;
use App\Models\SystemTaskRun;
use App\Notifications\CourseDeadlineReminderNotification;
use App\Services\Cursos\CourseNotificationAudience;
use App\Services\Cursos\CourseProgressRepository;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('lms:send-course-deadline-reminders')]
#[Description('Envía recordatorios de cursos pendientes dos días antes del vencimiento.')]
class SendCourseDeadlineReminderNotifications extends Command
{
    private const TIMEZONE = 'America/Santiago';

    /**
     * Execute the console command.
     */
    public function handle(CourseNotificationAudience $audience, CourseProgressRepository $courseProgressRepository): int
    {
        $taskRun = SystemTaskRun::start('lms:send-course-deadline-reminders');
        $sent = 0;

        try {
            $deadline = now(self::TIMEZONE)->startOfDay()->addDays(2);

            PlanificacionCurso::query()
                ->with(['curso.estamentos'])
                ->whereDate('fecha_fin', $deadline)
                ->chunkById(50, function ($planificaciones) use ($audience, $courseProgressRepository, &$sent): void {
                    foreach ($planificaciones as $planificacion) {
                        $sent += $this->notifyWorkersFor($planificacion, $audience, $courseProgressRepository);
                    }
                });

            $taskRun->markSuccess($sent, "Recordatorios de vencimiento enviados: {$sent}");
            $this->info("Recordatorios de vencimiento enviados: {$sent}");

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
                    $progreso = $progressByWorker[$worker->id] ?? 0;

                    if ($progreso >= 50) {
                        continue;
                    }

                    $dedupeKey = NotificationDelivery::deadlineReminderKey($worker, $curso, $planificacion);
                    $recorded = NotificationDelivery::recordOnce($dedupeKey, [
                        'user_id' => $worker->id,
                        'curso_id' => $curso->id,
                        'planificacion_curso_id' => $planificacion->id,
                        'type' => NotificationDelivery::CourseDeadlineReminder,
                    ]);

                    if (! $recorded) {
                        continue;
                    }

                    $worker->notify(new CourseDeadlineReminderNotification($curso, $planificacion, $progreso));
                    $sent++;
                }
            });

        return $sent;
    }
}
