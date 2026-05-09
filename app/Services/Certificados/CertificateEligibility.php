<?php

namespace App\Services\Certificados;

use App\Exceptions\CertificateNotEligible;
use App\Models\Curso;
use App\Models\IntentoEvaluacion;
use App\Models\User;

class CertificateEligibility
{
    public function ensure(User $user, Curso $curso): void
    {
        $modulosEvaluacion = $curso->modulos()
            ->where('tipo_contenido', 'evaluacion')
            ->with('evaluacion')
            ->get();

        $aprobado = false;
        foreach ($modulosEvaluacion as $modulo) {
            if ($modulo->evaluacion && IntentoEvaluacion::where('user_id', $user->id)
                ->where('evaluacion_id', $modulo->evaluacion->id)
                ->where('aprobado', true)
                ->exists()) {
                $aprobado = true;
                break;
            }
        }

        if (! $aprobado && $modulosEvaluacion->isNotEmpty()) {
            throw CertificateNotEligible::missingApprovedEvaluation($user, $curso);
        }
    }
}
