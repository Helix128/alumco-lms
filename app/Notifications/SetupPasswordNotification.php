<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class SetupPasswordNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::broker()->createToken($notifiable);

        $url = route('password.reset', [
            'token' => $token,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject('Configura tu cuenta en '.config('app.name'))
            ->greeting('Bienvenido/a, '.$notifiable->name.'!')
            ->line('Se ha creado una cuenta para ti en la plataforma de capacitaciones de Alumco.')
            ->line('Para comenzar, haz clic en el siguiente enlace y configura tu contraseña:')
            ->action('Configurar mi contraseña', $url)
            ->line('Este enlace expira en 60 minutos.')
            ->line('Si no solicitaste esta cuenta, puedes ignorar este correo.')
            ->salutation('Saludos, el equipo de Alumco');
    }
}
