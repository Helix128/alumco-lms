<?php

namespace App\Livewire\Support;

use App\Models\SupportTicket;
use App\Models\User;
use Livewire\Component;

class MyTickets extends Component
{
    protected $listeners = ['support-ticket-created' => '$refresh'];

    public function render()
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return view('livewire.support.my-tickets', [
            'tickets' => SupportTicket::query()
                ->whereBelongsTo($user, 'requester')
                ->latest('last_activity_at')
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }
}
