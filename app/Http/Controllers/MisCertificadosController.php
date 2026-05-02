<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Services\CertificadoService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MisCertificadosController extends Controller
{
    public function index()
    {
        $certificados = auth()->user()
            ->certificados()
            ->with('curso')
            ->latest()
            ->get();

        return view('mis-certificados.index', compact('certificados'));
    }

    public function descargar(Certificado $certificado, CertificadoService $service): StreamedResponse
    {
        abort_unless($certificado->user_id === auth()->id(), 403);

        return response()->streamDownload(
            fn () => print $service->output($certificado),
            $service->downloadFileName($certificado),
            ['Content-Type' => 'application/pdf']
        );
    }
}
