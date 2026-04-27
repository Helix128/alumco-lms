<?php

namespace App\Services;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\IntentoEvaluacion;
use App\Models\GlobalSetting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificadoService
{
    public function generarParaUsuario(User $user, Curso $curso): Certificado
    {
        // Si ya existe, retornar el existente
        $existente = Certificado::where('user_id', $user->id)
            ->where('curso_id', $curso->id)
            ->first();

        if ($existente) {
            return $existente;
        }

        // Verificar que el usuario aprobó al menos una evaluación del curso
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
            throw new \RuntimeException('El usuario no ha aprobado ninguna evaluación de este curso.');
        }

        $codigo = (string) Str::uuid();
        $capacitador = $curso->capacitador;
        $firmaRepLegal = GlobalSetting::get('firma_representante_legal', '');

        $pdf = Pdf::loadView('capacitador.certificados.plantilla', compact('user', 'curso', 'codigo', 'capacitador', 'firmaRepLegal'))
            ->setPaper('letter', 'landscape');

        $rutaRelativa = "certificados/{$codigo}.pdf";
        Storage::disk('public')->put($rutaRelativa, $pdf->output());

        return Certificado::create([
            'user_id'             => $user->id,
            'curso_id'            => $curso->id,
            'codigo_verificacion' => $codigo,
            'ruta_pdf'            => $rutaRelativa,
            'fecha_emision'       => now(),
        ]);
    }
}
