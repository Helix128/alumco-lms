<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\User;
use App\Services\CertificadoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificadoController extends Controller
{
    private function authorizeCurso(Curso $curso): void
    {
        abort_unless($curso->capacitador_id === auth()->id(), 403);
    }

    public function generar(Curso $curso, User $user, CertificadoService $service): RedirectResponse
    {
        $this->authorizeCurso($curso);

        try {
            $service->generarParaUsuario($user, $curso);
            return redirect()->back()->with('success', "Certificado generado para {$user->name}.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'No se pudo generar el certificado: ' . $e->getMessage());
        }
    }

    public function descargar(Certificado $certificado): StreamedResponse
    {
        abort_unless(
            $certificado->curso->capacitador_id === auth()->id(),
            403,
            'No tienes permiso para descargar este certificado.'
        );

        abort_unless(Storage::disk('public')->exists($certificado->ruta_pdf), 404, 'Archivo no encontrado.');

        return Storage::disk('public')->download(
            $certificado->ruta_pdf,
            "certificado_{$certificado->codigo_verificacion}.pdf"
        );
    }
}
