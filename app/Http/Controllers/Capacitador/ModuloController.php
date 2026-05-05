<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Capacitador\StoreModuloRequest;
use App\Http\Requests\Capacitador\UpdateModuloRequest;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\Modulo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ModuloController extends Controller
{
    public function create(Curso $curso): View
    {
        $this->authorize('manage', $curso);

        return view('capacitador.modulos.crear', [
            'curso' => $curso,
            'tipos' => Modulo::TIPO_LABELS,
        ]);
    }

    public function store(StoreModuloRequest $request, Curso $curso): RedirectResponse
    {
        $data = $request->validated();
        $data['curso_id'] = $curso->id;
        $data['orden'] = ($curso->modulos()->max('orden') ?? 0) + 1;

        if (isset($data['contenido'])) {
            $data['contenido'] = clean($data['contenido']);
        }

        if ($request->hasFile('ruta_archivo')) {
            $file = $request->file('ruta_archivo');
            $data['ruta_archivo'] = $file->store("modulos/{$curso->id}", 'public');
            $data['nombre_archivo_original'] = $file->getClientOriginalName();
        }

        DB::transaction(function () use ($data) {
            $modulo = Modulo::create($data);

            if ($modulo->tipo_contenido === 'evaluacion') {
                Evaluacion::create(['modulo_id' => $modulo->id]);
            }
        });

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Módulo creado correctamente.');
    }

    public function edit(Curso $curso, Modulo $modulo): View
    {
        $this->authorize('manage', $curso);
        abort_unless($modulo->curso_id === $curso->id, 404);

        return view('capacitador.modulos.editar', [
            'curso' => $curso,
            'modulo' => $modulo,
            'tipos' => Modulo::TIPO_LABELS,
        ]);
    }

    public function update(UpdateModuloRequest $request, Curso $curso, Modulo $modulo): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        $data = $request->validated();

        if (isset($data['contenido'])) {
            $data['contenido'] = clean($data['contenido']);
        }

        if ($request->hasFile('ruta_archivo')) {
            if ($modulo->ruta_archivo) {
                Storage::disk('public')->delete($modulo->ruta_archivo);
            }
            $file = $request->file('ruta_archivo');
            $data['ruta_archivo'] = $file->store("modulos/{$curso->id}", 'public');
            $data['nombre_archivo_original'] = $file->getClientOriginalName();
        }

        $modulo->update($data);

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Módulo actualizado correctamente.');
    }

    public function destroy(Curso $curso, Modulo $modulo): RedirectResponse
    {
        $this->authorize('manage', $curso);
        abort_unless($modulo->curso_id === $curso->id, 404);

        $rutaArchivo = $modulo->ruta_archivo;
        $orden = $modulo->orden;

        DB::transaction(function () use ($curso, $modulo, $orden) {
            $modulo->delete();

            $curso->modulos()
                ->where('orden', '>', $orden)
                ->orderBy('orden')
                ->each(function (Modulo $m, int $i) use ($orden) {
                    $m->update(['orden' => $orden + $i]);
                });
        });

        if ($rutaArchivo) {
            Storage::disk('public')->delete($rutaArchivo);
        }

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Módulo eliminado correctamente.');
    }

    public function evaluacion(Curso $curso, Modulo $modulo): View
    {
        $this->authorize('manage', $curso);
        abort_unless($modulo->curso_id === $curso->id, 404);
        abort_unless($modulo->tipo_contenido === 'evaluacion', 404);

        $modulo->load('evaluacion');
        abort_unless($modulo->evaluacion !== null, 404);

        return view('capacitador.modulos.evaluacion', [
            'curso' => $curso,
            'modulo' => $modulo,
        ]);
    }

    public function reordenar(Request $request, Curso $curso): JsonResponse
    {
        $this->authorize('manage', $curso);

        $request->validate(['orden' => 'required|array']);

        foreach ($request->input('orden') as $index => $moduloId) {
            $curso->modulos()->where('id', $moduloId)->update(['orden' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
