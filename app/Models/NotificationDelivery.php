<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

class NotificationDelivery extends Model
{
    public const CourseCompletedCertificate = 'course_completed_certificate';

    public const CourseAvailable = 'course_available';

    public const CourseDeadlineReminder = 'course_deadline_reminder';

    protected $fillable = [
        'user_id',
        'curso_id',
        'planificacion_curso_id',
        'certificado_id',
        'type',
        'dedupe_key',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @param  array{user_id: int, curso_id: int, planificacion_curso_id?: int|null, certificado_id?: int|null, type: string}  $attributes
     */
    public static function recordOnce(string $dedupeKey, array $attributes): bool
    {
        try {
            $delivery = self::query()->firstOrCreate(
                ['dedupe_key' => $dedupeKey],
                [...$attributes, 'sent_at' => now()],
            );
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return false;
            }

            throw $exception;
        }

        return $delivery->wasRecentlyCreated;
    }

    public static function certificateCompletedKey(User $user, Curso $curso, Certificado $certificado): string
    {
        return "certificate_completed:{$user->id}:{$curso->id}:{$certificado->id}";
    }

    public static function courseAvailableKey(User $user, Curso $curso, PlanificacionCurso $planificacion): string
    {
        return "course_available:{$user->id}:{$curso->id}:{$planificacion->id}";
    }

    public static function deadlineReminderKey(User $user, Curso $curso, PlanificacionCurso $planificacion): string
    {
        return "deadline_2_days:{$user->id}:{$curso->id}:{$planificacion->id}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function planificacionCurso(): BelongsTo
    {
        return $this->belongsTo(PlanificacionCurso::class);
    }

    public function certificado(): BelongsTo
    {
        return $this->belongsTo(Certificado::class);
    }
}
