<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject('Restablecer contraseña - '.config('app.name'))
            ->greeting('Hola, '.$notifiable->name.'!')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->line('Haz clic en el siguiente enlace para elegir una nueva contraseña:')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace expira en 60 minutos.')
            ->line('Si no solicitaste restablecer tu contraseña, puedes ignorar este correo.')
            ->salutation('Saludos, el equipo de Alumco');
    }
}
