<?php

namespace App\Actions\Support;

use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\User;
use App\Notifications\SupportTicketCreatedNotification;
use App\Notifications\SupportTicketRequesterNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreateSupportTicketAction
{
    /**
     * @param  array{contact_name?: string|null, contact_email?: string|null, category: string, subject: string, description: string, technical_context?: array<string, mixed>|null}  $data
     * @param  array<int, UploadedFile>  $attachments
     */
    public function handle(array $data, ?User $requester = null, array $attachments = []): SupportTicket
    {
        $ticket = DB::transaction(function () use ($data, $requester, $attachments): SupportTicket {
            $ticket = SupportTicket::create([
                'requester_user_id' => $requester?->id,
                'contact_name' => $requester?->name ?? ($data['contact_name'] ?? null),
                'contact_email' => $requester?->email ?? ($data['contact_email'] ?? null),
                'subject' => $data['subject'],
                'description' => $data['description'],
                'category' => $data['category'],
                'priority' => SupportTicket::PriorityMedium,
                'status' => SupportTicket::StatusNew,
                'technical_context' => $data['technical_context'] ?? null,
                'last_activity_at' => now(),
            ]);

            $this->storeAttachments($ticket, null, $attachments);

            return $ticket;
        });

        $developers = $this->developers();
        if ($developers->isNotEmpty()) {
            Notification::send($developers, (new SupportTicketCreatedNotification($ticket))->afterCommit());
        }

        if ($requester instanceof User) {
            $requester->notify((new SupportTicketRequesterNotification($ticket, 'created'))->afterCommit());
        } elseif ($ticket->contact_email) {
            Notification::route('mail', $ticket->contact_email)
                ->notify((new SupportTicketRequesterNotification($ticket, 'created'))->afterCommit());
        }

        return $ticket;
    }

    /**
     * @param  array<int, UploadedFile>  $attachments
     */
    public function storeAttachments(SupportTicket $ticket, mixed $message, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $path = $attachment->store("support-tickets/{$ticket->id}", 'local');

            SupportTicketAttachment::create([
                'support_ticket_id' => $ticket->id,
                'support_ticket_message_id' => $message?->id,
                'path' => $path,
                'original_name' => $attachment->getClientOriginalName(),
                'mime' => $attachment->getMimeType() ?: 'application/octet-stream',
                'size' => $attachment->getSize() ?: 0,
            ]);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function developers(): Collection
    {
        return User::role('Desarrollador')
            ->where('activo', true)
            ->whereNotNull('email')
            ->get();
    }
}
