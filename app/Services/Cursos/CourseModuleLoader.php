<?php

namespace App\Services\Cursos;

use App\Models\Curso;
use App\Models\User;

class CourseModuleLoader
{
    public function loadForUser(Curso $curso, User $user): Curso
    {
        $curso->load([
            'secciones' => function ($query) use ($user): void {
                $query->orderBy('orden')->with(['modulos' => function ($query) use ($user): void {
                    $query->orderBy('orden')->with(['progresos' => function ($query) use ($user): void {
                        $query->where('user_id', $user->id);
                    }]);
                }]);
            },
            'modulos' => function ($query) use ($user): void {
                $query->orderBy('orden')
                    ->with(['progresos' => function ($query) use ($user): void {
                        $query->where('user_id', $user->id);
                    }]);
            },
        ]);

        return $curso;
    }
}
