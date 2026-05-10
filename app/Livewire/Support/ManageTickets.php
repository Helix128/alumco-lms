<?php

namespace App\Livewire\Support;

use App\Actions\Support\CreateSupportTicketAction;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Notifications\SupportTicketRequesterNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageTickets extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'estado', except: '')]
    public string $status = '';

    #[Url(as: 'asignado', except: '')]
    public string $assigned = '';

    #[Url(as: 'ticket', except: null)]
    public ?int $selectedTicketId = null;

    public string $newStatus = SupportTicket::StatusInReview;

    public string $newPriority = SupportTicket::PriorityMedium;

    public bool $isInternalReply = false;

    public string $replyBody = '';

    /**
     * @var array<int, mixed>
     */
    public array $replyAttachments = [];

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isDesarrollador(), 403);

        if ($this->selectedTicketId !== null) {
            $ticket = $this->selectedTicket();

            if ($ticket === null) {
                $this->selectedTicketId = null;

                return;
            }

            $this->syncSelectionState($ticket);
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'assigned'], true)) {
            $this->resetPage();
        }
    }

    public function selectTicket(int $ticketId): void
    {
        $ticket = SupportTicket::query()->findOrFail($ticketId);

        $this->syncSelectionState($ticket);
    }

    public function assignToMe(): void
    {
        $ticket = $this->selectedTicket();

        if ($ticket === null) {
            return;
        }

        $status = $ticket->status === SupportTicket::StatusNew
            ? SupportTicket::StatusInReview
            : $ticket->status;

        $ticket->update([
            'assigned_to_id' => auth()->id(),
            'status' => $status,
            'last_activity_at' => now(),
        ]);

        $this->newStatus = $status;
    }

    public function updateTicket(): void
    {
        $ticket = $this->selectedTicket();

        if ($ticket === null) {
            return;
        }

        $data = $this->validate([
            'newStatus' => ['required', Rule::in(SupportTicket::Statuses)],
            'newPriority' => ['required', Rule::in(SupportTicket::Priorities)],
        ]);

        $wasResolved = $ticket->status === SupportTicket::StatusResolved;
        $wasWaitingUser = $ticket->status === SupportTicket::StatusWaitingUser;
        $isResolved = $data['newStatus'] === SupportTicket::StatusResolved;
        $isClosed = $data['newStatus'] === SupportTicket::StatusClosed;

        $ticket->update([
            'status' => $data['newStatus'],
            'priority' => $data['newPriority'],
            'last_activity_at' => now(),
            'resolved_at' => $isResolved ? ($ticket->resolved_at ?? now()) : null,
            'closed_at' => $isClosed ? ($ticket->closed_at ?? now()) : null,
        ]);

        $ticket->refresh();

        if ($isResolved && ! $wasResolved) {
            $this->notifyRequester($ticket, 'resolved');
        }

        if ($ticket->status === SupportTicket::StatusWaitingUser && ! $wasWaitingUser) {
            $this->notifyRequester($ticket, 'waiting_user');
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

    /**
     * @return array<int, string>
     */
    public function getStatusesProperty(): array
    {
        return SupportTicket::Statuses;
    }

    /**
     * @return array{critical: int, new: int, waiting: int, resolved_recent: int}
     */
    public function getCountersProperty(): array
    {
        return Cache::flexible('support_ticket_counters', [15, 60], function (): array {
            return [
                'new' => SupportTicket::query()->where('status', SupportTicket::StatusNew)->count(),
                'critical' => SupportTicket::query()->open()->where('priority', SupportTicket::PriorityCritical)->count(),
                'waiting' => SupportTicket::query()->where('status', SupportTicket::StatusWaitingUser)->count(),
                'resolved_recent' => SupportTicket::query()
                    ->where('status', SupportTicket::StatusResolved)
                    ->where('resolved_at', '>=', now()->subDays(7))
                    ->count(),
            ];
        });
    }

    public function getTicketsProperty(): LengthAwarePaginator
    {
        return SupportTicket::query()
            ->with(['requester', 'assignee'])
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%")
                        ->orWhereHas('requester', function (Builder $requester) use ($search): void {
                            $requester->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });

                    if (ctype_digit($search)) {
                        $inner->orWhereKey((int) $search);
                    }
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
                'assignee',
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
            'replyBody' => ['required', 'string', 'min:3', 'max:5000'],
            'replyAttachments' => ['array', 'max:3'],
            'replyAttachments.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'replyBody.required' => 'Escribe una respuesta.',
            'replyAttachments.max' => 'Puedes adjuntar hasta 3 capturas.',
            'replyAttachments.*.mimes' => 'Las capturas deben ser jpg, jpeg, png o webp.',
            'replyAttachments.*.max' => 'Cada captura puede pesar hasta 4 MB.',
        ]);

        $message = SupportTicketMessage::query()->create([
            'support_ticket_id' => $ticket->id,
            'author_user_id' => auth()->id(),
            'body' => $validated['replyBody'],
            'is_internal' => $this->isInternalReply,
        ]);

        app(CreateSupportTicketAction::class)->storeAttachments(
            $ticket,
            $message,
            $validated['replyAttachments'] ?? [],
        );

        $newStatus = $ticket->status;
        $resolvedAt = $ticket->resolved_at;

        if ($this->isInternalReply) {
            if ($ticket->status === SupportTicket::StatusNew) {
                $newStatus = SupportTicket::StatusInReview;
            }
        } elseif ($resolveAfterReply) {
            $newStatus = SupportTicket::StatusResolved;
            $resolvedAt = $ticket->resolved_at ?? now();
        } else {
            $newStatus = SupportTicket::StatusWaitingUser;
            $resolvedAt = null;
        }

        $ticket->update([
            'assigned_to_id' => $ticket->assigned_to_id ?: auth()->id(),
            'status' => $newStatus,
            'last_activity_at' => now(),
            'resolved_at' => $resolvedAt,
            'closed_at' => $newStatus === SupportTicket::StatusClosed ? ($ticket->closed_at ?? now()) : null,
        ]);

        $ticket->refresh();

        if (! $this->isInternalReply) {
            $this->notifyRequester($ticket, $resolveAfterReply ? 'resolved' : 'reply');
        }

        $this->reset(['replyBody', 'replyAttachments', 'isInternalReply']);
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
    }

    private function syncSelectionState(SupportTicket $ticket): void
    {
        $this->selectedTicketId = $ticket->id;
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
        $this->reset(['replyBody', 'replyAttachments', 'isInternalReply']);
    }

    private function notifyRequester(SupportTicket $ticket, string $type): void
    {
        if ($ticket->requester !== null) {
            $ticket->requester->notify(new SupportTicketRequesterNotification($ticket, $type));

            return;
        }

        $email = $ticket->requesterEmail();

        if ($email !== null && $email !== '') {
            Notification::route('mail', $email)
                ->notify(new SupportTicketRequesterNotification($ticket, $type));
        }
    }
}
