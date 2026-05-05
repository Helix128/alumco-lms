<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\User;
use App\Services\CertificadoService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificadoController extends Controller
{
    public function generar(Curso $curso, User $user, CertificadoService $service): RedirectResponse
    {
        $this->authorize('manage', $curso);

        try {
            $service->generarParaUsuario($user, $curso);

            return redirect()->back()->with('success', "Certificado generado para {$user->name}.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'No se pudo generar el certificado: '.$e->getMessage());
        }
    }

    public function descargar(Certificado $certificado, CertificadoService $service): StreamedResponse
    {
        $this->authorize('download', $certificado);

        return response()->streamDownload(
            fn () => print $service->output($certificado),
            $service->downloadFileName($certificado),
            ['Content-Type' => 'application/pdf']
        );
    }
}
