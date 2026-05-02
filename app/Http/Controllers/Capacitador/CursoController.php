<?php

namespace App\Http\Controllers\Capacitador;

use App\Actions\Cursos\DuplicateCourseAction;
use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Evaluacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CursoController extends Controller
{
    private function authorizeCurso(Curso $curso): void
    {
        if (auth()->user()->hasAdminAccess()) {
            return;
        }
        abort_unless($curso->capacitador_id === auth()->id(), 403);
    }

    public function index(): View
    {
        $query = auth()->user()->hasAdminAccess()
            ? Curso::query()
            : auth()->user()->cursosImpartidos();

        $cursos = $query
            ->with('capacitador')
            ->withCount(['modulos', 'estamentos', 'planificaciones'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('capacitador.cursos.index', compact('cursos'));
    }

    public function create(): View
    {
        return view('capacitador.cursos.crear');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen_portada' => 'nullable|image|max:4096',
            'color_promedio' => 'nullable|string|max:7',
            'auto_color' => 'nullable|boolean',
        ]);

        $data['capacitador_id'] = auth()->id();

        if ($request->hasFile('imagen_portada')) {
            $data['imagen_portada'] = $request->file('imagen_portada')
                ->store('portadas', 'public');
        }

        if ($request->boolean('auto_color')) {
            $data['color_promedio'] = null;
        }

        $curso = Curso::create($data);

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Curso creado correctamente.');
    }

    public function show(Curso $curso): View
    {
        $this->authorizeCurso($curso);

        $curso->load([
            'secciones' => fn ($q) => $q->orderBy('orden'),
            'secciones.modulos' => fn ($q) => $q->orderBy('orden'),
            'modulos' => fn ($q) => $q->whereNull('seccion_id')->orderBy('orden'),
            'modulos.evaluacion'
        ]);

        // Sanar módulos huérfanos: evaluacion creada pero sin registro en DB
        foreach ($curso->modulos as $modulo) {
            if ($modulo->tipo_contenido === 'evaluacion' && ! $modulo->evaluacion) {
                $evaluacion = Evaluacion::create([
                    'modulo_id' => $modulo->id,
                ]);
                $modulo->setRelation('evaluacion', $evaluacion);
            }
        }

        return view('capacitador.cursos.show', compact('curso'));
    }

    public function edit(Curso $curso): View
    {
        $this->authorizeCurso($curso);

        return view('capacitador.cursos.editar', compact('curso'));
    }

    public function update(Request $request, Curso $curso): RedirectResponse
    {
        $this->authorizeCurso($curso);

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen_portada' => 'nullable|image|max:4096',
            'color_promedio' => 'nullable|string|max:7',
            'auto_color' => 'nullable|boolean',
        ]);

        if ($request->hasFile('imagen_portada')) {
            if ($curso->imagen_portada) {
                Storage::disk('public')->delete($curso->imagen_portada);
            }
            $data['imagen_portada'] = $request->file('imagen_portada')
                ->store('portadas', 'public');
        }

        if ($request->boolean('auto_color')) {
            $data['color_promedio'] = null;
        }

        $curso->update($data);

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Curso $curso): RedirectResponse
    {
        $this->authorizeCurso($curso);

        // Eliminar archivos de módulos
        foreach ($curso->modulos as $modulo) {
            if ($modulo->ruta_archivo) {
                Storage::disk('public')->delete($modulo->ruta_archivo);
            }
        }

        // Eliminar portada
        if ($curso->imagen_portada) {
            Storage::disk('public')->delete($curso->imagen_portada);
        }

        $curso->delete();

        return redirect()->route('capacitador.cursos.index')
            ->with('success', 'Curso eliminado correctamente.');
    }

    public function duplicar(Request $request, Curso $curso, DuplicateCourseAction $action): RedirectResponse
    {
        $this->authorizeCurso($curso);

        $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        $nuevoCurso = $action->execute($curso, $request->titulo);

        return redirect()->route('capacitador.cursos.show', $nuevoCurso)
            ->with('success', 'Nueva versión del curso creada exitosamente. Ahora puedes editarla.');
    }
}
