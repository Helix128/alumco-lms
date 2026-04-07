<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ParticipanteController extends Controller
{
    private function authorizeCurso(Curso $curso): void
    {
        if (auth()->user()->hasAdminAccess()) {
            return;
        }
        abort_unless($curso->capacitador_id === auth()->id(), 403);
    }

    public function index(Curso $curso): View
    {
        $this->authorizeCurso($curso);

        $curso->load(['modulos', 'estamentos.users.estamento', 'estamentos.users.sede']);

        // Recopilar todos los usuarios de los estamentos asignados
        $usuarios = $curso->estamentos
            ->flatMap(fn($e) => $e->users)
            ->unique('id');

        // Para cada usuario calcular progreso y estado de certificado
        $moduloIds = $curso->modulos->pluck('id');

        $usuarios = $usuarios->map(function (User $user) use ($curso, $moduloIds) {
            $completados = \App\Models\ProgresoModulo::where('user_id', $user->id)
                ->whereIn('modulo_id', $moduloIds)
                ->where('completado', true)
                ->count();

            $total = $moduloIds->count();
            $user->progreso_porcentaje = $total > 0 ? (int) round(($completados / $total) * 100) : 0;
            $user->certificado = Certificado::where('user_id', $user->id)
                ->where('curso_id', $curso->id)
                ->first();

            return $user;
        });

        $todosEstamentos = Estamento::orderBy('nombre')->get();

        return view('capacitador.participantes.index', compact('curso', 'usuarios', 'todosEstamentos'));
    }

    public function syncEstamentos(Request $request, Curso $curso): RedirectResponse
    {
        $this->authorizeCurso($curso);

        $request->validate(['estamentos' => 'array', 'estamentos.*' => 'exists:estamentos,id']);

        $curso->estamentos()->sync($request->input('estamentos', []));

        return redirect()->route('capacitador.cursos.participantes.index', $curso)
            ->with('success', 'Estamentos actualizados correctamente.');
    }

    public function exportar(Curso $curso)
    {
        $this->authorizeCurso($curso);

        $curso->load(['modulos', 'estamentos.users.estamento', 'estamentos.users.sede']);

        $moduloIds = $curso->modulos->pluck('id');
        $usuarios = $curso->estamentos->flatMap(fn($e) => $e->users)->unique('id');

        $rows = $usuarios->map(function (User $user) use ($curso, $moduloIds) {
            $completados = \App\Models\ProgresoModulo::where('user_id', $user->id)
                ->whereIn('modulo_id', $moduloIds)
                ->where('completado', true)
                ->count();
            $total = $moduloIds->count();
            $progreso = $total > 0 ? (int) round(($completados / $total) * 100) : 0;

            $cert = Certificado::where('user_id', $user->id)
                ->where('curso_id', $curso->id)
                ->first();

            return [
                'Nombre'            => $user->name,
                'Email'             => $user->email,
                'Estamento'         => $user->estamento?->nombre ?? '—',
                'Sede'              => $user->sede?->nombre ?? '—',
                'Progreso (%)'      => $progreso,
                'Fecha Certificado' => $cert?->fecha_emision?->format('d/m/Y') ?? '—',
            ];
        })->values()->toArray();

        return Excel::download(new \App\Exports\ParticipantesCursoExport($rows), "participantes_{$curso->id}.xlsx");
    }
}
