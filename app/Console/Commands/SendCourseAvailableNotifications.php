<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\NotificationDelivery;
use App\Models\PlanificacionCurso;
use App\Models\User;
use App\Notifications\CourseAvailableNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('lms:send-course-available-notifications')]
#[Description('Envía correos a trabajadores cuando un curso queda disponible.')]
class SendCourseAvailableNotifications extends Command
{
    private const TIMEZONE = 'America/Santiago';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sent = 0;
        $today = now(self::TIMEZONE)->startOfDay();

        PlanificacionCurso::query()
            ->with(['curso.estamentos', 'curso.modulos'])
            ->where('fecha_inicio', '<=', $today)
            ->where('fecha_fin', '>=', $today)
            ->chunkById(50, function ($planificaciones) use (&$sent): void {
                foreach ($planificaciones as $planificacion) {
                    $sent += $this->notifyWorkersFor($planificacion);
                }
            });

        $this->info("Notificaciones de cursos disponibles enviadas: {$sent}");

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
                    if ($this->progressFor($worker, $curso) === 100) {
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
