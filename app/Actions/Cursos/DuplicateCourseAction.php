<?php

namespace App\Actions\Cursos;

use App\Models\Curso;
use App\Models\Pregunta;
use Illuminate\Support\Facades\DB;

class DuplicateCourseAction
{
    /**
     * Realiza una clonación profunda de un curso para crear una nueva versión.
     */
    public function execute(Curso $cursoOriginal, string $nuevoTitulo): Curso
    {
        return DB::transaction(function () use ($cursoOriginal, $nuevoTitulo) {
            // 1. Clonar el curso base
            $nuevoCurso = $cursoOriginal->replicate();
            $nuevoCurso->titulo = $nuevoTitulo;
            $nuevoCurso->curso_original_id = $cursoOriginal->id;
            $nuevoCurso->save();

            // 2. Clonar los módulos
            foreach ($cursoOriginal->modulos as $moduloOriginal) {
                $nuevoModulo = $moduloOriginal->replicate();
                $nuevoModulo->curso_id = $nuevoCurso->id;
                $nuevoModulo->save();

                // 3. Si el módulo tiene una evaluación, clonarla profundamente
                if ($moduloOriginal->tipo_contenido === 'evaluacion' && $moduloOriginal->evaluacion) {
                    $evaluacionOriginal = $moduloOriginal->evaluacion;

                    $nuevaEvaluacion = $evaluacionOriginal->replicate();
                    $nuevaEvaluacion->modulo_id = $nuevoModulo->id;
                    $nuevaEvaluacion->save();

                    // 4. Clonar las preguntas de la evaluación
                    foreach ($evaluacionOriginal->preguntas as $preguntaOriginal) {
                        $nuevaPregunta = $preguntaOriginal->replicate();
                        $nuevaPregunta->evaluacion_id = $nuevaEvaluacion->id;
                        $nuevaPregunta->save();

                        // 5. Clonar las opciones de la pregunta
                        foreach ($preguntaOriginal->opciones as $opcionOriginal) {
                            $nuevaOpcion = $opcionOriginal->replicate();
                            $nuevaOpcion->pregunta_id = $nuevaPregunta->id;
                            $nuevaOpcion->save();
                        }
                    }
                }
            }

            return $nuevoCurso;
        });
    }
}
