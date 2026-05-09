<?php

namespace App\Notifications;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Curso $curso,
        public PlanificacionCurso $planificacion,
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
            ->subject('Curso disponible: '.$this->curso->titulo)
            ->markdown('emails.notifications.course-available', [
                'name' => $notifiable->name,
                'courseTitle' => $this->curso->titulo,
                'courseUrl' => $url,
                'availableUntil' => $this->planificacion->fecha_fin->timezone('America/Santiago')->format('d/m/Y'),
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
            'course_url' => route('cursos.show', $this->curso),
        ];
    }
}
