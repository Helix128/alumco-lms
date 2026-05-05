<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Authorization\UserHierarchyService;

class UserPolicy
{
    public function __construct(
        private readonly UserHierarchyService $userHierarchyService
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->hasAdminAccess();
    }

    public function view(User $user, User $targetUser): bool
    {
        return $this->manage($user, $targetUser);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminAccess();
    }

    public function update(User $user, User $targetUser): bool
    {
        return $this->manage($user, $targetUser);
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $this->manage($user, $targetUser);
    }

    public function restore(User $user, User $targetUser): bool
    {
        return $this->manage($user, $targetUser);
    }

    public function forceDelete(User $user, User $targetUser): bool
    {
        return false;
    }

    /**
     * Administrador puede gestionar usuarios de menor rango.
     * Desarrollador puede gestionar colaboradores y capacitadores.
     */
    public function manage(User $user, User $targetUser): bool
    {
        return $this->userHierarchyService->canManageUser($user, $targetUser);
    }
}
