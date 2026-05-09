<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class SetupPasswordNotification extends Notification implements ShouldQueue
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
            ->markdown('emails.notifications.setup-password', [
                'name' => $notifiable->name,
                'setupUrl' => $url,
                'expiresInMinutes' => 60,
            ]);
    }
}
