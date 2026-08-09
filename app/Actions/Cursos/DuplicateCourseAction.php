<?php

namespace App\Actions\Cursos;

use App\Models\Curso;
use App\Services\Media\MediaAttachmentService;
use Illuminate\Support\Facades\DB;

class DuplicateCourseAction
{
    public function __construct(private readonly MediaAttachmentService $mediaAttachments) {}

    /**
     * Realiza una clonación profunda de un curso para crear una nueva versión.
     */
    public function execute(Curso $cursoOriginal, string $nuevoTitulo): Curso
    {
        $cursoOriginal->loadMissing('modulos.evaluacion.preguntas.opciones');

        return DB::transaction(function () use ($cursoOriginal, $nuevoTitulo) {
            $nuevoCurso = $cursoOriginal->replicate();
            $nuevoCurso->titulo = $nuevoTitulo;
            $nuevoCurso->curso_original_id = $cursoOriginal->id;
            $nuevoCurso->save();
            $this->mediaAttachments->copy($cursoOriginal, $nuevoCurso);

            foreach ($cursoOriginal->modulos as $moduloOriginal) {
                $nuevoModulo = $moduloOriginal->replicate();
                $nuevoModulo->curso_id = $nuevoCurso->id;
                $nuevoModulo->save();
                $this->mediaAttachments->copy($moduloOriginal, $nuevoModulo);

                if ($moduloOriginal->tipo_contenido === 'evaluacion' && $moduloOriginal->evaluacion) {
                    $evaluacionOriginal = $moduloOriginal->evaluacion;

                    $nuevaEvaluacion = $evaluacionOriginal->replicate();
                    $nuevaEvaluacion->modulo_id = $nuevoModulo->id;
                    $nuevaEvaluacion->save();

                    foreach ($evaluacionOriginal->preguntas as $preguntaOriginal) {
                        $nuevaPregunta = $preguntaOriginal->replicate();
                        $nuevaPregunta->evaluacion_id = $nuevaEvaluacion->id;
                        $nuevaPregunta->save();

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
