<?php

namespace App\Services\Authorization;

use App\Models\User;

class UserHierarchyService
{
    public function getHierarchyRank(User $user): int
    {
        if ($user->isDesarrollador()) {
            return 3;
        }
        if ($user->isAdmin()) {
            return 2;
        }

        return 1;
    }

    public function roleRank(string $roleName): int
    {
        return match ($roleName) {
            'Desarrollador' => 3,
            'Administrador' => 2,
            default => 1,
        };
    }

    public function canManageUser(User $actor, User $targetUser): bool
    {
        if ($actor->id === $targetUser->id) {
            return false;
        }

        if ($actor->isDesarrollador()) {
            return true;
        }

        return $this->getHierarchyRank($actor) > $this->getHierarchyRank($targetUser);
    }
}
