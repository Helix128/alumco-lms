<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SupportTicket $ticket) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo ticket de soporte #'.$this->ticket->id)
            ->markdown('emails.notifications.support-ticket-created', [
                'name' => $notifiable->name,
                'ticket' => $this->ticket,
                'ticketUrl' => route('dev.support.index', ['ticket' => $this->ticket->id]),
            ]);
    }
}
