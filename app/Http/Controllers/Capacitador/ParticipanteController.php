<?php

namespace App\Http\Controllers\Capacitador;

use App\Exports\ParticipantesCursoExport;
use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\ProgresoModulo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ParticipanteController extends Controller
{
    /**
     * Resuelve progreso y certificado para cada usuario en bulk (sin N+1).
     *
     * @param  Collection<int, User>  $usuarios
     * @param  Collection<int, int>  $moduloIds
     * @return Collection<int, User> Usuarios con `progreso_porcentaje` y `certificado` asignados
     */
    private function resolverParticipantes(Collection $usuarios, Curso $curso, Collection $moduloIds): Collection
    {
        $userIds = $usuarios->pluck('id');
        $total = $moduloIds->count();

        $progresos = ProgresoModulo::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('modulo_id', $moduloIds)
            ->where('completado', true)
            ->selectRaw('user_id, count(*) as completados')
            ->groupBy('user_id')
            ->pluck('completados', 'user_id');

        $certificados = Certificado::query()
            ->whereIn('user_id', $userIds)
            ->where('curso_id', $curso->id)
            ->get()
            ->keyBy('user_id');

        return $usuarios->map(function (User $user) use ($total, $progresos, $certificados) {
            $completados = $progresos->get($user->id, 0);
            $user->progreso_porcentaje = $total > 0 ? (int) round(($completados / $total) * 100) : 0;
            $user->certificado = $certificados->get($user->id);

            return $user;
        });
    }

    public function index(Curso $curso): View
    {
        $this->authorize('manage', $curso);

        $curso->load(['modulos', 'estamentos.users.estamento', 'estamentos.users.sede']);

        $usuarios = $curso->estamentos
            ->flatMap(fn ($e) => $e->users)
            ->unique('id');

        $moduloIds = $curso->modulos->pluck('id');
        $usuarios = $this->resolverParticipantes($usuarios, $curso, $moduloIds);

        $todosEstamentos = Estamento::orderBy('nombre')->get();

        return view('capacitador.participantes.index', compact('curso', 'usuarios', 'todosEstamentos'));
    }

    public function syncEstamentos(Request $request, Curso $curso): RedirectResponse
    {
        $this->authorize('manage', $curso);

        $request->validate(['estamentos' => 'array', 'estamentos.*' => 'exists:estamentos,id']);

        $curso->estamentos()->sync($request->input('estamentos', []));

        return redirect()->route('capacitador.cursos.participantes.index', $curso)
            ->with('success', 'Estamentos actualizados correctamente.');
    }

    public function exportar(Curso $curso)
    {
        $this->authorize('manage', $curso);

        $curso->load(['modulos', 'estamentos.users.estamento', 'estamentos.users.sede']);

        $moduloIds = $curso->modulos->pluck('id');
        $usuarios = $curso->estamentos->flatMap(fn ($e) => $e->users)->unique('id');
        $usuarios = $this->resolverParticipantes($usuarios, $curso, $moduloIds);

        $rows = $usuarios->map(fn (User $user) => [
            'RUT' => $user->rut ?? '—',
            'Nombre' => $user->name,
            'Email' => $user->email,
            'Estamento' => $user->estamento?->nombre ?? '—',
            'Sede' => $user->sede?->nombre ?? '—',
            'Progreso (%)' => $user->progreso_porcentaje,
            'Fecha Certificado' => $user->certificado?->fecha_emision?->format('d/m/Y') ?? '—',
        ])->values()->toArray();

        return Excel::download(new ParticipantesCursoExport($rows), "participantes_{$curso->id}.xlsx");
    }
}
