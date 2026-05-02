<?php

namespace App\Services;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\GlobalSetting;
use App\Models\IntentoEvaluacion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificadoService
{
    public function generarParaUsuario(User $user, Curso $curso): Certificado
    {
        $existente = Certificado::where('user_id', $user->id)
            ->where('curso_id', $curso->id)
            ->first();

        if ($existente) {
            $this->deleteStoredPdf($existente);

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

        return Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'codigo_verificacion' => $codigo,
            'ruta_pdf' => '',
            'fecha_emision' => now(),
        ]);
    }

    public function output(Certificado $certificado): string
    {
        $this->deleteStoredPdf($certificado);

        return $this->renderPdf(
            $certificado->user,
            $certificado->curso,
            $certificado->codigo_verificacion
        )->output();
    }

    private function renderPdf(User $user, Curso $curso, string $codigo): PDF
    {
        $capacitador = $curso->capacitador;
        $firmaRepLegal = GlobalSetting::get('firma_representante_legal', '');

        return PdfFacade::loadView('capacitador.certificados.plantilla', compact('user', 'curso', 'codigo', 'capacitador', 'firmaRepLegal'))
            ->setOptions([
                'default_paper_size' => 'letter',
                'default_paper_orientation' => 'portrait',
                'dpi' => 96,
                'enable_html5_parser' => true,
                'enable_font_subsetting' => true,
            ], true)
            ->setPaper('letter', 'portrait');
    }

    private function deleteStoredPdf(Certificado $certificado): void
    {
        if ($certificado->ruta_pdf !== '' && Storage::disk('public')->exists($certificado->ruta_pdf)) {
            Storage::disk('public')->delete($certificado->ruta_pdf);
        }

        if ($certificado->ruta_pdf !== '') {
            $certificado->forceFill(['ruta_pdf' => ''])->saveQuietly();
        }
    }
}
