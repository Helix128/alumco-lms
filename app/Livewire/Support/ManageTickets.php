<?php

namespace App\Livewire\Support;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Notifications\SupportTicketRequesterNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageTickets extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $assigned = '';

    public ?int $selectedTicketId = null;

    public string $newStatus = SupportTicket::StatusNew;

    public string $newPriority = SupportTicket::PriorityMedium;

    public bool $isInternalReply = false;

    public string $replyBody = '';

    /**
     * @var array<int, mixed>
     */
    public array $replyAttachments = [];

    protected string $paginationTheme = 'tailwind';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'assigned' => ['except' => ''],
        'selectedTicketId' => ['except' => null],
    ];

    public function selectTicket(int $ticketId): void
    {
        $this->selectedTicketId = $ticketId;
        $selected = $this->selectedTicket();
        if ($selected !== null) {
            $this->newStatus = $selected->status;
            $this->newPriority = $selected->priority;
        }
    }

    public function assignToMe(): void
    {
        $ticket = $this->selectedTicket();
        if ($ticket === null) {
            return;
        }

        $ticket->update([
            'assigned_to_id' => auth()->id(),
            'status' => $ticket->status === SupportTicket::StatusNew
                ? SupportTicket::StatusInReview
                : $ticket->status,
            'last_activity_at' => now(),
        ]);
    }

    public function updateTicket(): void
    {
        $ticket = $this->selectedTicket();
        if ($ticket === null) {
            return;
        }

        $ticket->update([
            'status' => $this->newStatus,
            'priority' => $this->newPriority,
            'last_activity_at' => now(),
            'resolved_at' => $this->newStatus === SupportTicket::StatusResolved ? now() : $ticket->resolved_at,
            'closed_at' => $this->newStatus === SupportTicket::StatusClosed ? now() : $ticket->closed_at,
        ]);

        if ($ticket->requester !== null) {
            if ($this->newStatus === SupportTicket::StatusResolved) {
                $ticket->requester->notify(new SupportTicketRequesterNotification($ticket->fresh(), 'resolved'));
            }

            if ($this->newStatus === SupportTicket::StatusWaitingUser) {
                $ticket->requester->notify(new SupportTicketRequesterNotification($ticket->fresh(), 'waiting_user'));
            }
        }
    }

    public function reply(): void
    {
        $this->persistReply(false);
    }

    public function replyAndResolve(): void
    {
        $this->persistReply(true);
    }

    public function closeTicket(): void
    {
        $ticket = $this->selectedTicket();
        if ($ticket === null) {
            return;
        }

        $ticket->update([
            'status' => SupportTicket::StatusClosed,
            'closed_at' => now(),
            'last_activity_at' => now(),
        ]);
        $this->newStatus = SupportTicket::StatusClosed;
    }

    public function getStatusesProperty(): array
    {
        return SupportTicket::Statuses;
    }

    public function getCountersProperty(): array
    {
        return [
            'critical' => SupportTicket::query()->open()->where('priority', SupportTicket::PriorityCritical)->count(),
            'new' => SupportTicket::query()->where('status', SupportTicket::StatusNew)->count(),
            'waiting' => SupportTicket::query()->where('status', SupportTicket::StatusWaitingUser)->count(),
        ];
    }

    public function getTicketsProperty()
    {
        return SupportTicket::query()
            ->with(['requester', 'assignee'])
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('subject', 'like', "%{$search}%")
                        ->orWhere('id', $search)
                        ->orWhereHas('requester', fn (Builder $requester) => $requester->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->assigned === 'mine', fn (Builder $query) => $query->where('assigned_to_id', auth()->id()))
            ->when($this->assigned === 'unassigned', fn (Builder $query) => $query->whereNull('assigned_to_id'))
            ->latest('last_activity_at')
            ->latest()
            ->paginate(12);
    }

    public function getSelectedTicketProperty(): ?SupportTicket
    {
        return $this->selectedTicket();
    }

    public function render(): View
    {
        return view('livewire.support.manage-tickets', [
            'tickets' => $this->tickets,
            'statuses' => $this->statuses,
            'counters' => $this->counters,
            'selectedTicket' => $this->selectedTicket,
        ]);
    }

    private function selectedTicket(): ?SupportTicket
    {
        if ($this->selectedTicketId === null) {
            return null;
        }

        return SupportTicket::query()
            ->with([
                'requester',
                'attachments',
                'messages' => fn ($query) => $query->with(['author', 'attachments'])->oldest(),
            ])
            ->find($this->selectedTicketId);
    }

    private function persistReply(bool $resolveAfterReply): void
    {
        $ticket = $this->selectedTicket();
        if ($ticket === null) {
            return;
        }

        $validated = $this->validate([
            'replyBody' => ['required', 'string', 'min:2', 'max:5000'],
            'replyAttachments' => ['array', 'max:3'],
            'replyAttachments.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $message = SupportTicketMessage::query()->create([
            'support_ticket_id' => $ticket->id,
            'author_user_id' => auth()->id(),
            'body' => $validated['replyBody'],
            'is_internal' => $this->isInternalReply,
        ]);

        foreach ($validated['replyAttachments'] ?? [] as $file) {
            $path = $file->store("support/{$ticket->id}", 'local');
            $message->attachments()->create([
                'support_ticket_id' => $ticket->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $newStatus = $resolveAfterReply && ! $this->isInternalReply
            ? SupportTicket::StatusResolved
            : ($ticket->status === SupportTicket::StatusNew ? SupportTicket::StatusInReview : $ticket->status);

        $ticket->update([
            'status' => $newStatus,
            'assigned_to_id' => $ticket->assigned_to_id ?: auth()->id(),
            'last_activity_at' => now(),
            'resolved_at' => $newStatus === SupportTicket::StatusResolved ? now() : $ticket->resolved_at,
        ]);

        $this->replyBody = '';
        $this->replyAttachments = [];
        $this->isInternalReply = false;
        $this->newStatus = $newStatus;
    }
}
