<?php

namespace App\Notifications;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoursePlanningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Curso $curso,
        public PlanificacionCurso $planificacion,
        public string $type,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->type === 'updated'
            ? 'Actualización de planificación: '.$this->curso->titulo
            : 'Nueva planificación de curso: '.$this->curso->titulo;

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.notifications.course-planning', [
                'name' => $notifiable->name,
                'courseTitle' => $this->curso->titulo,
                'courseUrl' => route('cursos.show', $this->curso),
                'startDate' => $this->planificacion->fecha_inicio->timezone('America/Santiago')->format('d/m/Y'),
                'endDate' => $this->planificacion->fecha_fin->timezone('America/Santiago')->format('d/m/Y'),
                'type' => $this->type,
            ]);
    }
}
