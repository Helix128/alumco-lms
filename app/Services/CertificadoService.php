<?php

namespace App\Services;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Notifications\CourseCompletedCertificateNotification;
use App\Services\Certificados\CertificateEligibility;
use App\Services\Certificados\CertificatePdfRenderer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificadoService
{
    public function __construct(
        private readonly CertificateEligibility $certificateEligibility,
        private readonly CertificatePdfRenderer $certificatePdfRenderer
    ) {}

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

        $this->certificateEligibility->ensure($user, $curso);

        $codigo = (string) Str::uuid();

        $certificado = DB::transaction(function () use ($user, $curso, $codigo) {
            return Certificado::create([
                'user_id' => $user->id,
                'curso_id' => $curso->id,
                'codigo_verificacion' => $codigo,
                'ruta_pdf' => '',
                'fecha_emision' => now(),
            ]);
        });

        $this->notifyCertificateAvailable($user, $curso, $certificado);

        return $certificado;
    }

    public function output(Certificado $certificado): string
    {
        $this->deleteStoredPdf($certificado);

        return $this->certificatePdfRenderer->render($certificado)->output();
    }

    public function downloadFileName(Certificado $certificado): string
    {
        $certificado->loadMissing(['user', 'curso']);

        $cursoSlug = $this->fileNameSegment($certificado->curso?->titulo, 'curso');
        $personaSlug = $this->fileNameSegment($certificado->user?->name, 'participante');

        return "certificado_{$cursoSlug}_{$personaSlug}.pdf";
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
