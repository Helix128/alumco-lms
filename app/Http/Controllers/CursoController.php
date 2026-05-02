<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CursoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hoy = now()->startOfDay();
        $isPreview = session('preview_mode', false);

        if ($isPreview) {
            $cursos = $this->previewCoursesQuery($user)->get();
            $cursos->each(fn (Curso $curso) => $curso->is_preview = true);
        } else {
            $cursos = $user->estamento
                ? $user->estamento->cursos()
                    ->with($this->courseRelationsFor($user))
                    ->get()
                : collect();
        }

        $vigentes = collect();
        $completados = collect();

        foreach ($cursos as $curso) {
            $progreso = $curso->progresoParaUsuario($user);
            $curso->progreso_calculado = $progreso;

            if ($progreso === 100 && ! isset($curso->is_preview)) {
                $completados->push($curso);

                continue;
            }

            $tieneActiva = $curso->planificaciones->contains(
                fn ($p) => $p->fecha_inicio->lte($hoy)
                    && $p->fecha_fin->gte($hoy)
                    && ($p->sede_id === null || $p->sede_id === $user->sede_id)
            );

            // Si es preview y no tiene planificación activa, forzamos su visualización como vigente
            if ($tieneActiva || isset($curso->is_preview)) {
                $vigentes->push($curso);
            }
        }

        return view('cursos.index', compact('vigentes', 'completados', 'user'));
    }

    public function show(Curso $curso)
    {
        $user = auth()->user();
        $hoy = now()->startOfDay();
        $isPreview = session('preview_mode', false);

        $esAutorOAdmin = $user->hasAdminAccess() || $curso->capacitador_id === $user->id;
        $estaAsociadoPorEstamento = $this->belongsToUserEstamento($curso, $user);

        // Bypass de restricciones si estamos en modo vista previa y está asociado o es admin/dev.
        if (! ($isPreview && ($esAutorOAdmin || $estaAsociadoPorEstamento))) {
            // Verificar que el curso tiene una planificación activa
            $curso->load('planificaciones');

            $tieneActiva = $curso->planificaciones->contains(
                fn ($p) => $p->fecha_inicio->lte($hoy)
                    && $p->fecha_fin->gte($hoy)
                    && ($p->sede_id === null || $p->sede_id === $user->sede_id)
            );

            if (! $tieneActiva) {
                $proxima = $curso->planificaciones
                    ->filter(fn ($p) => $p->fecha_inicio->gt($hoy)
                        && ($p->sede_id === null || $p->sede_id === $user->sede_id))
                    ->sortBy('fecha_inicio')
                    ->first();

                $mensaje = $proxima
                    ? 'Este curso aún no ha iniciado. Estará disponible el '.$proxima->fecha_inicio->format('d/m/Y').'.'
                    : 'Este curso no tiene un periodo de disponibilidad activo.';

                abort(403, $mensaje);
            }

            // Verificar que el curso pertenece al estamento del usuario
            if ($user->estamento) {
                abort_unless(
                    $user->estamento->cursos()->where('cursos.id', $curso->id)->exists(),
                    403,
                    'No tienes acceso a este curso.'
                );
            } else {
                abort(403, 'No tienes acceso a este curso.');
            }
        }

        // Eager-load secciones + módulos + progresos del usuario (evita N+1)
        $curso->load([
            'secciones' => function ($query) use ($user) {
                $query->orderBy('orden')->with(['modulos' => function ($q) use ($user) {
                    $q->orderBy('orden')->with(['progresos' => function ($qp) use ($user) {
                        $qp->where('user_id', $user->id);
                    }]);
                }]);
            },
            'modulos' => function ($query) use ($user) {
                // Cargamos TODOS los módulos para que la lógica de acceso sea coherente
                $query->orderBy('orden')
                    ->with(['progresos' => function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    }]);
            }
        ]);

        $progreso = $curso->progresoParaUsuario($user);

        return view('cursos.show', compact('curso', 'progreso'));
    }

    /**
     * @return array<int|string, mixed>
     */
    private function courseRelationsFor(User $user): array
    {
        return [
            'planificaciones',
            'modulos' => fn ($q) => $q->orderBy('orden')
                ->with(['progresos' => fn ($q) => $q->where('user_id', $user->id)]),
        ];
    }

    private function previewCoursesQuery(User $user): Builder
    {
        return Curso::with($this->courseRelationsFor($user))
            ->when(! $user->hasAdminAccess(), function (Builder $query) use ($user): void {
                $query->where(function (Builder $query) use ($user): void {
                    $query->where('capacitador_id', $user->id)
                        ->when($user->estamento_id, function (Builder $query) use ($user): void {
                            $query->orWhereHas('estamentos', function (Builder $query) use ($user): void {
                                $query->where('estamentos.id', $user->estamento_id);
                            });
                        });
                });
            });
    }

    private function belongsToUserEstamento(Curso $curso, User $user): bool
    {
        if (! $user->estamento_id) {
            return false;
        }

        return $curso->estamentos()
            ->where('estamentos.id', $user->estamento_id)
            ->exists();
    }
}
