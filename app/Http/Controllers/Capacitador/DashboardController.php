<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\ProgresoModulo;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $cursosQuery = $user->hasAdminAccess()
            ? Curso::query()
            : $user->cursosImpartidos();

        $cursos = $cursosQuery
            ->withCount(['modulos', 'estamentos'])
            ->orderByDesc('created_at')
            ->get();

        // Participantes únicos con algún progreso en los cursos del capacitador
        $cursoIds = $cursos->pluck('id');
        $totalParticipantes = ProgresoModulo::whereHas('modulo', fn($q) => $q->whereIn('curso_id', $cursoIds))
            ->distinct('user_id')
            ->count('user_id');

        $totalCertificados = Certificado::whereIn('curso_id', $cursoIds)->count();

        $stats = [
            'cursos'          => $cursos->count(),
            'participantes'   => $totalParticipantes,
            'certificados'    => $totalCertificados,
        ];

        $ultimosCursos = $cursos->take(5);

        return view('capacitador.dashboard', compact('stats', 'ultimosCursos', 'cursos'));
    }
}
