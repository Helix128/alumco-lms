<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Support\Facades\Storage;

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

    public function descargar(Certificado $certificado)
    {
        abort_unless($certificado->user_id === auth()->id(), 403);
        abort_unless(Storage::disk('public')->exists($certificado->ruta_pdf), 404);

        return Storage::disk('public')->download(
            $certificado->ruta_pdf,
            'certificado_' . $certificado->codigo_verificacion . '.pdf'
        );
    }
}
