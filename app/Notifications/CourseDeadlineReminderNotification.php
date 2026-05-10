<?php

namespace App\Notifications;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseDeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Curso $curso,
        public PlanificacionCurso $planificacion,
        public int $progreso,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('cursos.show', $this->curso);

        return (new MailMessage)
            ->subject('Tu capacitación vence pronto: '.$this->curso->titulo)
            ->markdown('emails.notifications.course-deadline-reminder', [
                'name' => $notifiable->name,
                'courseTitle' => $this->curso->titulo,
                'courseUrl' => $url,
                'progress' => $this->progreso,
                'deadlineDate' => $this->planificacion->fecha_fin->format('d/m/Y'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'curso_id' => $this->curso->id,
            'planificacion_curso_id' => $this->planificacion->id,
            'progress' => $this->progreso,
            'course_url' => route('cursos.show', $this->curso),
        ];
    }
}
