<?php

namespace App\Policies;

use App\Models\Certificado;
use App\Models\User;

class CertificadoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Certificado $certificado): bool
    {
        return $this->download($user, $certificado);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Certificado $certificado): bool
    {
        return $user->hasAdminAccess();
    }

    public function delete(User $user, Certificado $certificado): bool
    {
        return $user->hasAdminAccess();
    }

    public function restore(User $user, Certificado $certificado): bool
    {
        return $user->hasAdminAccess();
    }

    public function forceDelete(User $user, Certificado $certificado): bool
    {
        return false;
    }

    /**
     * Dueño del certificado o capacitador del curso o administrador puede descargar.
     */
    public function download(User $user, Certificado $certificado): bool
    {
        if ($user->hasAdminAccess()) {
            return true;
        }

        if ($certificado->user_id === $user->id) {
            return true;
        }

        return $certificado->curso->capacitador_id === $user->id;
    }
}
