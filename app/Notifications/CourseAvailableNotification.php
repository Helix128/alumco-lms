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
            ->subject('Curso disponible - '.$this->curso->titulo)
            ->greeting('Hola, '.$notifiable->name.'.')
            ->line('El curso "'.$this->curso->titulo.'" ya está disponible para realizarlo.')
            ->line('Puedes ingresar desde ahora y avanzar en sus módulos.')
            ->action('Realizar curso', $url)
            ->line('El curso estará disponible hasta el '.$this->planificacion->fecha_fin->format('d/m/Y').'.')
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
            'course_url' => route('cursos.show', $this->curso),
        ];
    }
}
