<?php

namespace App\Services;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\GlobalSetting;
use App\Models\IntentoEvaluacion;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Notifications\CourseCompletedCertificateNotification;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
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
            $this->notifyCertificateAvailable($user, $curso, $existente);

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

        $certificado = Certificado::create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'codigo_verificacion' => $codigo,
            'ruta_pdf' => '',
            'fecha_emision' => now(),
        ]);

        $this->notifyCertificateAvailable($user, $curso, $certificado);

        return $certificado;
    }

    public function output(Certificado $certificado): string
    {
        $this->deleteStoredPdf($certificado);

        return $this->renderPdf($certificado)->output();
    }

    public function downloadFileName(Certificado $certificado): string
    {
        $certificado->loadMissing(['user', 'curso']);

        $cursoSlug = $this->fileNameSegment($certificado->curso?->titulo, 'curso');
        $personaSlug = $this->fileNameSegment($certificado->user?->name, 'participante');

        return "certificado_{$cursoSlug}_{$personaSlug}.pdf";
    }

    private function renderPdf(Certificado $certificado): PDF
    {
        $certificado->loadMissing(['user', 'curso.capacitador']);

        $user = $certificado->user;
        $curso = $certificado->curso;
        $capacitador = $curso->capacitador;
        $firmaRepLegal = GlobalSetting::get('firma_representante_legal', '');
        $codigo = $certificado->codigo_verificacion;
        $verificationUrl = route('certificados.verificar.show', $codigo);
        $qrCodeDataUri = $this->qrCodeDataUri($verificationUrl);
        $codigoDisplayLines = $this->codigoDisplayLines($codigo);
        $fechaEmision = $certificado->fecha_emision ?? $certificado->created_at;

        return PdfFacade::loadView('capacitador.certificados.plantilla', compact('user', 'curso', 'codigo', 'capacitador', 'firmaRepLegal', 'verificationUrl', 'qrCodeDataUri', 'codigoDisplayLines', 'fechaEmision'))
            ->setOptions([
                'default_paper_size' => 'letter',
                'default_paper_orientation' => 'portrait',
                'dpi' => 96,
                'enable_html5_parser' => true,
                'enable_font_subsetting' => true,
            ], true)
            ->setPaper('letter', 'portrait');
    }

    private function qrCodeDataUri(string $verificationUrl): string
    {
        return (new Builder(
            writer: new PngWriter,
            data: $verificationUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 240,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build()->getDataUri();
    }

    /**
     * @return list<string>
     */
    private function codigoDisplayLines(string $codigo): array
    {
        $parts = explode('-', $codigo);

        if (count($parts) === 5) {
            return [
                implode('-', array_slice($parts, 0, 3)),
                implode('-', array_slice($parts, 3)),
            ];
        }

        return str_split($codigo, 18);
    }

    private function fileNameSegment(?string $value, string $fallback): string
    {
        $slug = Str::slug($value ?: $fallback);
        $slug = trim(Str::limit($slug, 80, ''), '-');

        return $slug !== '' ? $slug : $fallback;
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

    private function notifyCertificateAvailable(User $user, Curso $curso, Certificado $certificado): void
    {
        $dedupeKey = NotificationDelivery::certificateCompletedKey($user, $curso, $certificado);

        $recorded = NotificationDelivery::recordOnce($dedupeKey, [
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'certificado_id' => $certificado->id,
            'type' => NotificationDelivery::CourseCompletedCertificate,
        ]);

        if ($recorded) {
            $user->notify(new CourseCompletedCertificateNotification($curso, $certificado));
        }
    }
}
