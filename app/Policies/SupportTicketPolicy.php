<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDesarrollador();
    }

    public function view(User $user, SupportTicket $supportTicket): bool
    {
        return $user->isDesarrollador() || $supportTicket->requester_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SupportTicket $supportTicket): bool
    {
        return $user->isDesarrollador();
    }

    public function manage(User $user, SupportTicket $supportTicket): bool
    {
        return $user->isDesarrollador();
    }
}
