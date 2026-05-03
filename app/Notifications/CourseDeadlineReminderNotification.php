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
            ->subject('Tu curso vence pronto - '.$this->curso->titulo)
            ->greeting('Hola, '.$notifiable->name.'.')
            ->line('El curso "'.$this->curso->titulo.'" termina el '.$this->planificacion->fecha_fin->format('d/m/Y').'.')
            ->line('Tu avance actual es de '.$this->progreso.'%. Aún tienes más de la mitad pendiente.')
            ->action('Continuar curso', $url)
            ->line('Ingresa cuanto antes para completar los módulos pendientes.')
            ->salutation('Saludos, el equipo de Alumco');
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
