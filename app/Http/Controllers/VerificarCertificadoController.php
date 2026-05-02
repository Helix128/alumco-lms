<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VerificarCertificadoController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $codigo = $this->normalizeCodigo((string) $request->query('codigo', ''));

        if ($codigo !== '') {
            return redirect()->route('certificados.verificar.show', $codigo);
        }

        return view('certificados.verificar', [
            'certificado' => null,
            'codigo' => '',
            'wasSearched' => false,
        ]);
    }

    public function show(string $codigo): View
    {
        $codigo = $this->normalizeCodigo($codigo);

        $certificado = Certificado::query()
            ->with(['user', 'curso'])
            ->where('codigo_verificacion', $codigo)
            ->first();

        return view('certificados.verificar', [
            'certificado' => $certificado,
            'codigo' => $codigo,
            'wasSearched' => true,
        ]);
    }

    private function normalizeCodigo(string $codigo): string
    {
        return Str::of($codigo)
            ->trim()
            ->lower()
            ->toString();
    }
}
