<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\SeccionCurso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeccionCursoController extends Controller
{
    private function authorizeCurso(Curso $curso): void
    {
        if (auth()->user()->hasAdminAccess()) {
            return;
        }
        abort_unless($curso->capacitador_id === auth()->id(), 403);
    }

    public function store(Request $request, Curso $curso): RedirectResponse
    {
        $this->authorizeCurso($curso);

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        $maxOrden = $curso->secciones()->max('orden') ?? 0;
        $data['curso_id'] = $curso->id;
        $data['orden'] = $maxOrden + 1;

        SeccionCurso::create($data);

        return redirect()->back()->with('success', 'Sección creada correctamente.');
    }

    public function update(Request $request, Curso $curso, SeccionCurso $seccion): RedirectResponse
    {
        $this->authorizeCurso($curso);
        abort_unless($seccion->curso_id === $curso->id, 404);

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        $seccion->update($data);

        return redirect()->back()->with('success', 'Sección actualizada correctamente.');
    }

    public function destroy(Curso $curso, SeccionCurso $seccion): RedirectResponse
    {
        $this->authorizeCurso($curso);
        abort_unless($seccion->curso_id === $curso->id, 404);

        // Los módulos asociados quedarán huérfanos (seccion_id = null) gracias al nullOnDelete en la migración
        $seccion->delete();

        return redirect()->back()->with('success', 'Sección eliminada. Los módulos asociados ahora están sin sección.');
    }

    public function reordenar(Request $request, Curso $curso)
    {
        $this->authorizeCurso($curso);

        $estructura = $request->validate([
            'secciones' => 'required|array',
            'secciones.*.id' => 'required', // Puede ser int o string "new_..."
            'secciones.*.modulos' => 'present|array',
            'modulos_sueltos' => 'present|array',
        ]);

        // 1. Procesar secciones
        foreach ($estructura['secciones'] as $index => $secData) {
            $seccionId = $secData['id'];

            // Si es una sección nueva creada vía Drag & Drop
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

            // 2. Reordenar módulos dentro de esta sección
            foreach ($secData['modulos'] as $modIndex => $moduloId) {
                $curso->modulos()->where('id', $moduloId)->update([
                    'seccion_id' => $seccionId,
                    'orden' => $modIndex + 1,
                ]);
            }
        }

        // 3. Reordenar módulos sueltos (legacy o si quedan)
        foreach ($estructura['modulos_sueltos'] as $modIndex => $moduloId) {
            $curso->modulos()->where('id', $moduloId)->update([
                'seccion_id' => null,
                'orden' => $modIndex + 1,
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
