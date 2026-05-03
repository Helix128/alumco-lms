<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\NotificationDelivery;
use App\Models\PlanificacionCurso;
use App\Models\User;
use App\Notifications\CourseDeadlineReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('lms:send-course-deadline-reminders')]
#[Description('Envía recordatorios de cursos pendientes dos días antes del vencimiento.')]
class SendCourseDeadlineReminderNotifications extends Command
{
    private const TIMEZONE = 'America/Santiago';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sent = 0;
        $deadline = now(self::TIMEZONE)->startOfDay()->addDays(2);

        PlanificacionCurso::query()
            ->with(['curso.estamentos'])
            ->whereDate('fecha_fin', $deadline)
            ->chunkById(50, function ($planificaciones) use (&$sent): void {
                foreach ($planificaciones as $planificacion) {
                    $sent += $this->notifyWorkersFor($planificacion);
                }
            });

        $this->info("Recordatorios de vencimiento enviados: {$sent}");

        return self::SUCCESS;
    }

    private function notifyWorkersFor(PlanificacionCurso $planificacion): int
    {
        $curso = $planificacion->curso;

        if (! $curso instanceof Curso) {
            return 0;
        }

        $estamentoIds = $curso->estamentos->pluck('id');

        if ($estamentoIds->isEmpty()) {
            return 0;
        }

        $sent = 0;

        User::query()
            ->role('Trabajador')
            ->where('activo', true)
            ->whereIn('estamento_id', $estamentoIds)
            ->when($planificacion->sede_id, function (Builder $query) use ($planificacion): void {
                $query->where('sede_id', $planificacion->sede_id);
            })
            ->chunkById(100, function ($workers) use ($curso, $planificacion, &$sent): void {
                foreach ($workers as $worker) {
                    $progreso = $this->progressFor($worker, $curso);

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

    private function progressFor(User $user, Curso $curso): int
    {
        $curso->unsetRelation('modulos');
        $curso->load(['modulos' => function ($query) use ($user): void {
            $query->orderBy('orden')
                ->with(['progresos' => fn ($query) => $query->where('user_id', $user->id)]);
        }]);

        return $curso->progresoParaUsuario($user);
    }
}
