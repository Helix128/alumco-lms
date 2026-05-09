<?php

namespace App\Livewire\Support;

use App\Actions\Support\CreateSupportTicketAction;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Notifications\SupportTicketRequesterNotification;
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

    #[Url(as: 'estado', except: '')]
    public string $status = '';

    #[Url(as: 'prioridad', except: '')]
    public string $priority = '';

    #[Url(as: 'categoria', except: '')]
    public string $category = '';

    #[Url(as: 'asignado', except: '')]
    public string $assigned = '';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'ticket', except: null)]
    public ?int $selectedTicketId = null;

    public string $replyBody = '';

    public bool $isInternalReply = false;

    public string $newStatus = SupportTicket::StatusInReview;

    public string $newPriority = SupportTicket::PriorityMedium;

    /**
     * @var array<int, mixed>
     */
    public array $replyAttachments = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->isDesarrollador(), 403);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'priority', 'category', 'assigned', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function selectTicket(int $ticketId): void
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        $this->authorizeDeveloper($ticket);
        $this->selectedTicketId = $ticket->id;
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
        $this->reset(['replyBody', 'replyAttachments', 'isInternalReply']);
    }

    public function assignToMe(): void
    {
        $ticket = $this->selectedTicket();
        $ticket->update([
            'assigned_to_id' => auth()->id(),
            'status' => $ticket->status === SupportTicket::StatusNew ? SupportTicket::StatusInReview : $ticket->status,
        ]);
    }

    public function updateTicket(): void
    {
        $ticket = $this->selectedTicket();

        $data = $this->validate([
            'newStatus' => ['required', Rule::in(SupportTicket::Statuses)],
            'newPriority' => ['required', Rule::in(SupportTicket::Priorities)],
        ]);

        $wasResolved = $ticket->status === SupportTicket::StatusResolved;
        $isResolved = $data['newStatus'] === SupportTicket::StatusResolved;
        $isClosed = $data['newStatus'] === SupportTicket::StatusClosed;
        $movedToWaitingUser = $ticket->status !== SupportTicket::StatusWaitingUser
            && $data['newStatus'] === SupportTicket::StatusWaitingUser;

        $ticket->update([
            'status' => $data['newStatus'],
            'priority' => $data['newPriority'],
            'resolved_at' => $isResolved && ! $wasResolved ? now() : $ticket->resolved_at,
            'closed_at' => $isClosed ? now() : $ticket->closed_at,
            'last_activity_at' => now(),
        ]);

        if ($isResolved && ! $wasResolved) {
            $this->notifyRequester($ticket->refresh(), 'resolved');
        } elseif ($movedToWaitingUser) {
            $this->notifyRequester($ticket->refresh(), 'waiting_user');
        }
    }

    public function reply(CreateSupportTicketAction $attachmentsAction): void
    {
        $ticket = $this->selectedTicket();

        $data = $this->validate([
            'replyBody' => ['required', 'string', 'min:3', 'max:5000'],
            'replyAttachments' => ['array', 'max:3'],
            'replyAttachments.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'replyBody.required' => 'Escribe una respuesta.',
            'replyAttachments.max' => 'Puedes adjuntar hasta 3 capturas.',
            'replyAttachments.*.mimes' => 'Las capturas deben ser jpg, jpeg, png o webp.',
            'replyAttachments.*.max' => 'Cada captura puede pesar hasta 4 MB.',
        ]);

        $message = SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'author_user_id' => auth()->id(),
            'body' => $data['replyBody'],
            'is_internal' => $this->isInternalReply,
        ]);

        $attachmentsAction->storeAttachments($ticket, $message, $data['replyAttachments'] ?? []);

        if (! $this->isInternalReply) {
            $ticket->update([
                'status' => SupportTicket::StatusWaitingUser,
                'last_activity_at' => now(),
            ]);
            $this->notifyRequester($ticket->refresh(), 'reply');
        } else {
            $ticket->touch('last_activity_at');
        }

        $this->reset(['replyBody', 'replyAttachments', 'isInternalReply']);
        $this->newStatus = $ticket->status;
    }

    public function replyAndResolve(CreateSupportTicketAction $attachmentsAction): void
    {
        $this->reply($attachmentsAction);

        $ticket = $this->selectedTicket();
        if ($ticket->status !== SupportTicket::StatusResolved) {
            $ticket->update([
                'status' => SupportTicket::StatusResolved,
                'resolved_at' => now(),
                'last_activity_at' => now(),
            ]);
            $this->notifyRequester($ticket->refresh(), 'resolved');
            $this->newStatus = SupportTicket::StatusResolved;
        }
    }

    public function closeTicket(): void
    {
        $ticket = $this->selectedTicket();
        $ticket->update([
            'status' => SupportTicket::StatusClosed,
            'closed_at' => now(),
            'last_activity_at' => now(),
        ]);
        $this->newStatus = SupportTicket::StatusClosed;
    }

    public function render()
    {
        abort_unless(auth()->user()?->isDesarrollador(), 403);

        $query = SupportTicket::query()
            ->with(['requester', 'assignee'])
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->priority !== '', fn ($query) => $query->where('priority', $this->priority))
            ->when($this->category !== '', fn ($query) => $query->where('category', $this->category))
            ->when($this->assigned === 'mine', fn ($query) => $query->where('assigned_to_id', auth()->id()))
            ->when($this->assigned === 'unassigned', fn ($query) => $query->whereNull('assigned_to_id'))
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(fn ($query) => $query
                    ->where('subject', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('contact_name', 'like', $term)
                    ->orWhere('contact_email', 'like', $term));
            })
            ->latest('last_activity_at')
            ->latest();

        $selectedTicket = $this->selectedTicketId
            ? SupportTicket::with([
                'requester',
                'assignee',
                'attachments',
                'messages' => fn ($q) => $q->orderBy('created_at', 'asc'),
                'messages.author',
                'messages.attachments',
            ])->find($this->selectedTicketId)
            : null;

        if ($selectedTicket && $this->newStatus === '') {
            $this->newStatus = $selectedTicket->status;
            $this->newPriority = $selectedTicket->priority;
        }

        $counters = Cache::flexible('support_ticket_counters', [15, 60], function (): array {
            return [
                'new' => SupportTicket::where('status', SupportTicket::StatusNew)->count(),
                'critical' => SupportTicket::open()->where('priority', SupportTicket::PriorityCritical)->count(),
                'waiting' => SupportTicket::where('status', SupportTicket::StatusWaitingUser)->count(),
                'resolved_recent' => SupportTicket::where('status', SupportTicket::StatusResolved)
                    ->where('resolved_at', '>=', now()->subDays(7))
                    ->count(),
            ];
        });

        return view('livewire.support.manage-tickets', [
            'tickets' => $query->paginate(12),
            'selectedTicket' => $selectedTicket,
            'statuses' => SupportTicket::Statuses,
            'priorities' => SupportTicket::Priorities,
            'categories' => SupportTicket::Categories,
            'counters' => $counters,
        ]);
    }

    private function selectedTicket(): SupportTicket
    {
        $ticket = SupportTicket::findOrFail($this->selectedTicketId);
        $this->authorizeDeveloper($ticket);

        return $ticket;
    }

    private function authorizeDeveloper(SupportTicket $ticket): void
    {
        abort_unless(auth()->user()?->can('manage', $ticket), 403);
    }

    private function notifyRequester(SupportTicket $ticket, string $type): void
    {
        if ($ticket->requester instanceof User) {
            $ticket->requester->notify(new SupportTicketRequesterNotification($ticket, $type));

            return;
        }

        if ($ticket->contact_email) {
            Notification::route('mail', $ticket->contact_email)
                ->notify(new SupportTicketRequesterNotification($ticket, $type));
        }
    }
}
