<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Modulo;
use App\Models\ProgresoModulo;

class ModuloController extends Controller
{
    public function show(Curso $curso, Modulo $modulo)
    {
        abort_if($modulo->curso_id !== $curso->id, 404);

        $user = auth()->user();

        // Cargar todos los módulos del curso con progresos del usuario (para estaAccesiblePara)
        $curso->load(['modulos' => function ($query) use ($user) {
            $query->orderBy('orden')
                  ->with(['progresos' => function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  }]);
        }]);

        // Sincronizar el módulo actual con el de la colección cargada (relaciones incluidas)
        $moduloCargado = $curso->modulos->find($modulo->id);

        abort_unless(
            $moduloCargado->estaAccesiblePara($user, $curso),
            403,
            'Este módulo aún está bloqueado. Completa los módulos anteriores primero.'
        );

        $progreso      = $curso->progresoParaUsuario($user);
        $totalModulos  = $curso->modulos->count();
        $moduloActual  = $curso->modulos->search(fn ($m) => $m->id === $modulo->id) + 1;
        $siguiente     = $curso->modulos->where('orden', '>', $modulo->orden)->first();

        if ($moduloCargado->tipo_contenido === 'evaluacion') {
            $moduloCargado->load('evaluacion.preguntas.opciones');

            if (! $moduloCargado->evaluacion) {
                return redirect()->route('cursos.show', $curso)
                    ->with('error', 'Esta evaluación no está disponible todav&iacute;a.');
            }

            return view('modulos.evaluacion', [
                'curso'  => $curso,
                'modulo' => $moduloCargado,
                'progreso' => $progreso,
            ]);
        }

        return view('modulos.capsula', [
            'curso'        => $curso,
            'modulo'       => $moduloCargado,
            'progreso'     => $progreso,
            'moduloActual' => $moduloActual,
            'totalModulos' => $totalModulos,
            'siguiente'    => $siguiente,
        ]);
    }

    public function completar(Curso $curso, Modulo $modulo)
    {
        abort_if($modulo->curso_id !== $curso->id, 404);

        ProgresoModulo::updateOrCreate(
            ['user_id' => auth()->id(), 'modulo_id' => $modulo->id],
            ['completado' => true, 'fecha_completado' => now()]
        );

        $action = request()->input('action', 'next');

        if ($action === 'course') {
            return redirect()->route('cursos.show', $curso)
                ->with('success', 'Módulo completado.');
        }

        // Cargar módulos para encontrar el siguiente
        $siguiente = $curso->modulos()->where('orden', '>', $modulo->orden)->orderBy('orden')->first();

        if ($siguiente) {
            return redirect()->route('modulos.show', [$curso, $siguiente]);
        }

        return redirect()->route('cursos.show', $curso)
            ->with('success', '¡Curso completado!');
    }
}
