<?php

namespace App\Actions\Support;

use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\SupportTicketCreatedNotification;
use App\Notifications\SupportTicketRequesterNotification;
use Illuminate\Support\Facades\Notification;

class CreateSupportTicketAction
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, mixed>  $attachments
     */
    public function handle(array $payload, ?User $requester = null, array $attachments = []): SupportTicket
    {
        $ticket = SupportTicket::query()->create([
            'requester_user_id' => $requester?->id,
            'contact_name' => $requester?->name ?? ($payload['contact_name'] ?? null),
            'contact_email' => $requester?->email ?? ($payload['contact_email'] ?? null),
            'subject' => (string) $payload['subject'],
            'description' => (string) $payload['description'],
            'category' => (string) $payload['category'],
            'priority' => SupportTicket::PriorityMedium,
            'status' => SupportTicket::StatusNew,
            'technical_context' => $payload['technical_context'] ?? null,
            'last_activity_at' => now(),
        ]);

        foreach ($attachments as $attachment) {
            $path = $attachment->store("support-tickets/{$ticket->id}", 'local');

            $ticket->attachments()->create([
                'path' => $path,
                'original_name' => $attachment->getClientOriginalName(),
                'mime' => $attachment->getClientMimeType(),
                'size' => $attachment->getSize(),
            ]);
        }

        $this->notifyDevelopers($ticket);
        $this->notifyRequesterOnCreate($ticket, $requester);

        return $ticket;
    }

    private function notifyDevelopers(SupportTicket $ticket): void
    {
        $developers = User::query()
            ->where('activo', true)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Desarrollador'))
            ->get();

        Notification::send($developers, new SupportTicketCreatedNotification($ticket));
    }

    private function notifyRequesterOnCreate(SupportTicket $ticket, ?User $requester): void
    {
        $notification = new SupportTicketRequesterNotification($ticket, 'created');

        if ($requester !== null) {
            $requester->notify($notification);

            return;
        }

        $email = $ticket->contact_email;
        if ($email !== null && $email !== '') {
            Notification::route('mail', $email)->notify($notification);
        }
    }
}
