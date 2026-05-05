<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Capacitador\ReorderSeccionesRequest;
use App\Http\Requests\Capacitador\StoreSeccionCursoRequest;
use App\Http\Requests\Capacitador\UpdateSeccionCursoRequest;
use App\Models\Curso;
use App\Models\SeccionCurso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SeccionCursoController extends Controller
{
    public function store(StoreSeccionCursoRequest $request, Curso $curso): RedirectResponse
    {
        $this->authorize('manage', $curso);

        $data = $request->validated();
        $maxOrden = $curso->secciones()->max('orden') ?? 0;
        $data['curso_id'] = $curso->id;
        $data['orden'] = $maxOrden + 1;

        SeccionCurso::create($data);

        return redirect()->back()->with('success', 'Sección creada correctamente.');
    }

    public function update(UpdateSeccionCursoRequest $request, Curso $curso, SeccionCurso $seccion): RedirectResponse
    {
        $this->authorize('manage', $curso);
        abort_unless($seccion->curso_id === $curso->id, 404);

        $seccion->update($request->validated());

        return redirect()->back()->with('success', 'Sección actualizada correctamente.');
    }

    public function destroy(Curso $curso, SeccionCurso $seccion): RedirectResponse
    {
        $this->authorize('manage', $curso);
        abort_unless($seccion->curso_id === $curso->id, 404);

        $seccion->delete();

        return redirect()->back()->with('success', 'Sección eliminada. Los módulos asociados ahora están sin sección.');
    }

    public function reordenar(ReorderSeccionesRequest $request, Curso $curso)
    {
        $this->authorize('manage', $curso);

        $estructura = $request->validated();

        DB::transaction(function () use ($estructura, $curso) {
            foreach ($estructura['secciones'] as $index => $secData) {
                $seccionId = $secData['id'];

                if (str_starts_with($seccionId, 'new_')) {
                    $nuevaSeccion = SeccionCurso::create([
                        'curso_id' => $curso->id,
                        'titulo' => 'Nueva Sección',
                        'orden' => $index + 1,
                    ]);
                    $seccionId = $nuevaSeccion->id;
                } else {
                    SeccionCurso::where('id', $seccionId)
                        ->where('curso_id', $curso->id)
                        ->update(['orden' => $index + 1]);
                }

                foreach ($secData['modulos'] as $modIndex => $moduloId) {
                    $curso->modulos()->where('id', $moduloId)->update([
                        'seccion_id' => $seccionId,
                        'orden' => $modIndex + 1,
                    ]);
                }
            }

            foreach ($estructura['modulos_sueltos'] as $modIndex => $moduloId) {
                $curso->modulos()->where('id', $moduloId)->update([
                    'seccion_id' => null,
                    'orden' => $modIndex + 1,
                ]);
            }
        });

        return response()->json(['status' => 'success']);
    }
}
