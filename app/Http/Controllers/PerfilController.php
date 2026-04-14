<?php

namespace App\Http\Controllers;

class PerfilController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['sede', 'estamento']);

        $totalCursos = $user->estamento?->cursos()->count() ?? 0;

        $cursosCompletados = $user->certificados()->count();

        $cursosEnProgreso = 0;
        if ($user->estamento && $totalCursos > 0) {
            $ids = $user->certificados()->pluck('curso_id');
            $cursosEnProgreso = $user->estamento->cursos()
                ->whereNotIn('cursos.id', $ids)
                ->whereHas('modulos.progresos', fn ($q) => $q->where('user_id', $user->id)->where('completado', true))
                ->count();
        }

        $certificados = $user->certificados()->with('curso')->latest()->take(5)->get();

        return view('perfil.index', compact(
            'user',
            'totalCursos',
            'cursosCompletados',
            'cursosEnProgreso',
            'certificados'
        ));
    }
}
