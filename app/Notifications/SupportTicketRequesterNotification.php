<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketRequesterNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
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
        $subject = match ($this->type) {
            'created' => 'Recibimos tu ticket de soporte #'.$this->ticket->id,
            'resolved' => 'Ticket de soporte resuelto #'.$this->ticket->id,
            'waiting_user' => 'Tu ticket requiere acción #'.$this->ticket->id,
            default => 'Respuesta a tu ticket de soporte #'.$this->ticket->id,
        };

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.notifications.support-ticket-requester', [
                'ticket' => $this->ticket,
                'type' => $this->type,
                'ticketUrl' => $this->ticket->requester_user_id ? route('support.show', $this->ticket) : null,
            ]);
    }
}
