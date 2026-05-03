<?php

namespace App\Notifications;

use App\Models\Certificado;
use App\Models\Curso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseCompletedCertificateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Curso $curso,
        public Certificado $certificado,
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
        $url = route('mis-certificados.descargar', $this->certificado);

        return (new MailMessage)
            ->subject('Certificado disponible - '.$this->curso->titulo)
            ->greeting('Hola, '.$notifiable->name.'.')
            ->line('Completaste el curso "'.$this->curso->titulo.'".')
            ->line('Tu certificado ya está disponible para descarga en la plataforma.')
            ->action('Descargar certificado', $url)
            ->line('Por seguridad, deberás iniciar sesión para descargarlo.')
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
            'certificado_id' => $this->certificado->id,
            'download_url' => route('mis-certificados.descargar', $this->certificado),
        ];
    }
}
