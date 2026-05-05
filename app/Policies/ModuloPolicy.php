<?php

namespace App\Policies;

use App\Models\Modulo;
use App\Models\User;

class ModuloPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Modulo $modulo): bool
    {
        return $this->manage($user, $modulo);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Modulo $modulo): bool
    {
        return $this->manage($user, $modulo);
    }

    public function delete(User $user, Modulo $modulo): bool
    {
        return $this->manage($user, $modulo);
    }

    public function restore(User $user, Modulo $modulo): bool
    {
        return $this->manage($user, $modulo);
    }

    public function forceDelete(User $user, Modulo $modulo): bool
    {
        return false;
    }

    /**
     * Owner del curso (capacitador) o administrador puede gestionar módulos.
     */
    public function manage(User $user, Modulo $modulo): bool
    {
        return $user->hasAdminAccess() || $modulo->curso->capacitador_id === $user->id;
    }
}
