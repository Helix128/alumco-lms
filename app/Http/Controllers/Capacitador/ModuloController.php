<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
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
    private function authorizeCurso(Curso $curso): void
    {
        if (auth()->user()->hasAdminAccess()) {
            return;
        }
        abort_unless($curso->capacitador_id === auth()->id(), 403);
    }

    private function getMimeRules(string $tipoContenido): string
    {
        return match ($tipoContenido) {
            'video'  => 'mimes:mp4',
            'pdf'    => 'mimes:pdf',
            'ppt'    => 'mimes:ppt,pptx',
            'imagen' => 'mimes:jpeg,png,jpg,gif,webp',
            default  => '',
        };
    }

    public function create(Curso $curso): View
    {
        $this->authorizeCurso($curso);

        return view('capacitador.modulos.crear', [
            'curso'      => $curso,
            'tipos'      => Modulo::TIPO_LABELS,
        ]);
    }

    public function store(Request $request, Curso $curso): RedirectResponse
    {
        $this->authorizeCurso($curso);

        $mimeRules = $this->getMimeRules($request->input('tipo_contenido', ''));
        $fileRule = 'nullable|file|max:512000' . ($mimeRules ? '|' . $mimeRules : '');

        $data = $request->validate([
            'titulo'           => 'required|string|max:255',
            'tipo_contenido'   => 'required|in:' . implode(',', Modulo::TIPOS),
            'duracion_minutos' => 'nullable|integer|min:1',
            'contenido'        => 'nullable|string',
            'ruta_archivo'     => $fileRule,
        ]);

        $data['curso_id'] = $curso->id;
        $data['orden']    = ($curso->modulos()->max('orden') ?? 0) + 1;

        if ($request->hasFile('ruta_archivo')) {
            $file = $request->file('ruta_archivo');
            $data['ruta_archivo'] = $file->store("modulos/{$curso->id}", 'public');
            $data['nombre_archivo_original'] = $file->getClientOriginalName();
        }

        $modulo = DB::transaction(function () use ($data, $curso) {
            $modulo = Modulo::create($data);

            if ($modulo->tipo_contenido === 'evaluacion') {
                Evaluacion::create([
                    'modulo_id'              => $modulo->id,
                    'puntos_aprobacion'      => 0,
                    'max_intentos_semanales' => 2,
                ]);
            }

            return $modulo;
        });

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Módulo creado correctamente.');
    }

    public function edit(Curso $curso, Modulo $modulo): View
    {
        $this->authorizeCurso($curso);
        abort_unless($modulo->curso_id === $curso->id, 404);

        return view('capacitador.modulos.editar', [
            'curso'  => $curso,
            'modulo' => $modulo,
            'tipos'  => Modulo::TIPO_LABELS,
        ]);
    }

    public function update(Request $request, Curso $curso, Modulo $modulo): RedirectResponse
    {
        $this->authorizeCurso($curso);
        abort_unless($modulo->curso_id === $curso->id, 404);

        $mimeRules = $this->getMimeRules($modulo->tipo_contenido);
        $fileRule = 'nullable|file|max:512000' . ($mimeRules ? '|' . $mimeRules : '');

        $data = $request->validate([
            'titulo'           => 'required|string|max:255',
            'duracion_minutos' => 'nullable|integer|min:1',
            'contenido'        => 'nullable|string',
            'ruta_archivo'     => $fileRule,
        ]);

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
        $this->authorizeCurso($curso);
        abort_unless($modulo->curso_id === $curso->id, 404);

        if ($modulo->ruta_archivo) {
            Storage::disk('public')->delete($modulo->ruta_archivo);
        }

        $orden = $modulo->orden;
        $modulo->delete();

        // Reordenar los módulos restantes
        $curso->modulos()
            ->where('orden', '>', $orden)
            ->orderBy('orden')
            ->each(function (Modulo $m, int $i) use ($orden) {
                $m->update(['orden' => $orden + $i]);
            });

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Módulo eliminado correctamente.');
    }

    public function evaluacion(Curso $curso, Modulo $modulo): View
    {
        $this->authorizeCurso($curso);
        abort_unless($modulo->curso_id === $curso->id, 404);
        abort_unless($modulo->tipo_contenido === 'evaluacion', 404);

        $modulo->load('evaluacion');
        abort_unless($modulo->evaluacion !== null, 404);

        return view('capacitador.modulos.evaluacion', [
            'curso'  => $curso,
            'modulo' => $modulo,
        ]);
    }

    public function reordenar(Request $request, Curso $curso): JsonResponse
    {
        $this->authorizeCurso($curso);

        $request->validate(['orden' => 'required|array']);

        foreach ($request->input('orden') as $index => $moduloId) {
            $curso->modulos()->where('id', $moduloId)->update(['orden' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
