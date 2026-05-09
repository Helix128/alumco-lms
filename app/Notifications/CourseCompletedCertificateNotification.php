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
            ->subject('Tu certificado ya está disponible: '.$this->curso->titulo)
            ->markdown('emails.notifications.course-certificate-ready', [
                'name' => $notifiable->name,
                'courseTitle' => $this->curso->titulo,
                'downloadUrl' => $url,
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
            'certificado_id' => $this->certificado->id,
            'download_url' => route('mis-certificados.descargar', $this->certificado),
        ];
    }
}
