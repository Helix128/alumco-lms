<?php

namespace App\Services\Cursos;

use App\Models\Curso;
use App\Models\User;

class CourseAccessService
{
    public function canViewAsWorker(User $user, Curso $curso): bool
    {
        if ($user->hasAdminAccess() || $curso->capacitador_id === $user->id) {
            return true;
        }

        if (! $user->estamento_id) {
            return false;
        }

        $belongsToEstamento = $curso->estamentos()
            ->where('estamentos.id', $user->estamento_id)
            ->exists();

        return $belongsToEstamento && $curso->estaDisponiblePara($user);
    }
}
