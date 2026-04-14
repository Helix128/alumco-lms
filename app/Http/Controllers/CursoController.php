<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hoy  = now()->startOfDay();

        $cursos = $user->estamento
            ? $user->estamento->cursos()
                ->with([
                    'planificaciones',
                    'modulos' => fn ($q) => $q->orderBy('orden')
                        ->with(['progresos' => fn ($q) => $q->where('user_id', $user->id)]),
                ])
                ->get()
            : collect();

        $vigentes    = collect();
        $completados = collect();

        foreach ($cursos as $curso) {
            $progreso = $curso->progresoParaUsuario($user);
            $curso->progreso_calculado = $progreso;

            if ($progreso === 100) {
                $completados->push($curso);
                continue;
            }

            $tieneActiva = $curso->planificaciones->contains(
                fn ($p) => $p->fecha_inicio->lte($hoy) && $p->fecha_fin->gte($hoy)
            );

            if ($tieneActiva) {
                $vigentes->push($curso);
            }
        }

        return view('cursos.index', compact('vigentes', 'completados', 'user'));
    }

    public function show(Curso $curso)
    {
        $user = auth()->user();
        $hoy  = now()->startOfDay();

        // Verificar que el curso tiene una planificación activa
        $curso->load('planificaciones');

        $tieneActiva = $curso->planificaciones->contains(
            fn ($p) => $p->fecha_inicio->lte($hoy) && $p->fecha_fin->gte($hoy)
        );

        if (! $tieneActiva) {
            $proxima = $curso->planificaciones
                ->where('fecha_inicio', '>', $hoy)
                ->sortBy('fecha_inicio')
                ->first();

            $mensaje = $proxima
                ? 'Este curso aún no ha iniciado. Estará disponible el ' . $proxima->fecha_inicio->format('d/m/Y') . '.'
                : 'Este curso no tiene un periodo de disponibilidad activo.';

            abort(403, $mensaje);
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
