<?php

namespace App\Services\Cursos;

use App\Models\Curso;
use App\Models\Modulo;
use App\Models\User;

class ModuleAccessService
{
    public function authorizeAccess(Curso $curso, Modulo $modulo, User $user): Modulo
    {
        abort_if($modulo->curso_id !== $curso->id, 404);

        if ($user->hasAdminAccess() || $curso->capacitador_id === $user->id) {
            return $modulo;
        }

        return $this->authorizeWorkerAccess($curso, $modulo, $user);
    }

    public function authorizeWorkerAccess(Curso $curso, Modulo $modulo, User $user): Modulo
    {
        $this->authorizeCourseAccess($curso, $user);
        $this->loadCourseModulesFor($curso, $user);

        $loadedModule = $curso->modulos->find($modulo->id);
        abort_if(! $loadedModule, 404);
        abort_unless(
            $loadedModule->estaAccesiblePara($user, $curso),
            403,
            'Este módulo aún está bloqueado. Completa los módulos anteriores primero.'
        );

        return $loadedModule;
    }

    private function authorizeCourseAccess(Curso $curso, User $user): void
    {
        $isPreview = session('preview_mode', false);
        $isAuthorOrAdmin = $user->hasAdminAccess() || $curso->capacitador_id === $user->id;
        $belongsToEstamento = $this->belongsToUserEstamento($curso, $user);

        if ($isPreview && ($isAuthorOrAdmin || $belongsToEstamento)) {
            return;
        }

        abort_unless($curso->estaDisponiblePara($user), 403, 'Este curso no tiene un periodo de disponibilidad activo.');
        abort_unless($belongsToEstamento, 403, 'No tienes acceso a este curso.');
    }

    private function belongsToUserEstamento(Curso $curso, User $user): bool
    {
        if (! $user->estamento_id) {
            return false;
        }

        return $curso->estamentos()
            ->where('estamentos.id', $user->estamento_id)
            ->exists();
    }

    private function loadCourseModulesFor(Curso $curso, User $user): void
    {
        $curso->load(['modulos' => function ($query) use ($user): void {
            $query->orderBy('orden')
                ->with(['progresos' => function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                }]);
        }]);
    }
}
