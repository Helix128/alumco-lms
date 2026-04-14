<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cursos = $user->estamento
            ? $user->estamento->cursos()
                ->with(['modulos' => fn ($q) => $q->orderBy('orden')
                    ->with(['progresos' => fn ($q) => $q->where('user_id', $user->id)])])
                ->get()
            : collect();

        return view('cursos.index', compact('cursos', 'user'));
    }

    public function show(Curso $curso)
    {
        $user = auth()->user();

        // Verificar si el curso ya inició
        if (now()->startOfDay() < $curso->fecha_inicio) {
            abort(403, 'Este curso aún no ha iniciado. Estará disponible el ' . $curso->fecha_inicio->format('d/m/Y') . '.');
        }

        // Verificar que el curso pertenece al estamento del usuario
        if ($user->estamento) {
            abort_unless(
                $user->estamento->cursos()->where('cursos.id', $curso->id)->exists(),
                403,
                'No tienes acceso a este curso.'
            );
        } else {
            abort(403, 'No tienes acceso a este curso.');
        }

        // Eager-load módulos + progresos del usuario (evita N+1)
        $curso->load(['modulos' => function ($query) use ($user) {
            $query->orderBy('orden')
                  ->with(['progresos' => function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  }]);
        }]);

        $progreso = $curso->progresoParaUsuario($user);

        return view('cursos.show', compact('curso', 'progreso'));
    }
}
