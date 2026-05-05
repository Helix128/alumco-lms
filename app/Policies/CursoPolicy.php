<?php

namespace App\Policies;

use App\Models\Curso;
use App\Models\User;

class CursoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Curso $curso): bool
    {
        return $this->manage($user, $curso);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Curso $curso): bool
    {
        return $this->manage($user, $curso);
    }

    public function delete(User $user, Curso $curso): bool
    {
        return $this->manage($user, $curso);
    }

    public function restore(User $user, Curso $curso): bool
    {
        return $this->manage($user, $curso);
    }

    public function forceDelete(User $user, Curso $curso): bool
    {
        return false;
    }

    /**
     * Owner del curso o administrador puede gestionarlo.
     * Regla canónica usada por todos los controladores del capacitador.
     */
    public function manage(User $user, Curso $curso): bool
    {
        return $user->hasAdminAccess() || $curso->capacitador_id === $user->id;
    }
}
