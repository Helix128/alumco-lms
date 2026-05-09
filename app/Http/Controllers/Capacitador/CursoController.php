<?php

namespace App\Http\Controllers\Capacitador;

use App\Actions\Cursos\DuplicateCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Capacitador\StoreCursoRequest;
use App\Http\Requests\Capacitador\UpdateCursoRequest;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Services\Analytics\LearningAnalyticsService;
use App\Services\Cursos\AverageCourseCoverColor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CursoController extends Controller
{
    public function __construct(
        private readonly AverageCourseCoverColor $averageCourseCoverColor
    ) {}

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

    public function store(StoreCursoRequest $request): RedirectResponse
    {
        $courseAttributes = $request->validated();
        $courseAttributes['capacitador_id'] = auth()->id();

        if ($request->hasFile('imagen_portada')) {
            $courseAttributes['imagen_portada'] = $request->file('imagen_portada')
                ->store('portadas', 'public');
        }

        if ($request->boolean('auto_color')) {
            $courseAttributes['color_promedio'] = $this->averageCourseCoverColor->fromPublicPath($courseAttributes['imagen_portada'] ?? null);
        }

        $curso = Curso::create($courseAttributes);

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Curso creado correctamente.');
    }

    public function show(Curso $curso, LearningAnalyticsService $analyticsService): View
    {
        $this->authorize('manage', $curso);

        $curso->load([
            'secciones' => fn ($q) => $q->orderBy('orden'),
            'secciones.modulos' => fn ($q) => $q->orderBy('orden'),
            'modulos' => fn ($q) => $q->whereNull('seccion_id')->orderBy('orden'),
            'modulos.evaluacion',
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

        $learningSummary = $analyticsService->courseSummary($curso);

        return view('capacitador.cursos.show', compact('curso', 'learningSummary'));
    }

    public function edit(Curso $curso): View
    {
        $this->authorize('manage', $curso);

        return view('capacitador.cursos.editar', compact('curso'));
    }

    public function update(UpdateCursoRequest $request, Curso $curso): RedirectResponse
    {
        $courseAttributes = $request->validated();
        $imagenPortadaPath = $curso->imagen_portada;

        if ($request->hasFile('imagen_portada')) {
            if ($curso->imagen_portada) {
                Storage::disk('public')->delete($curso->imagen_portada);
            }
            $courseAttributes['imagen_portada'] = $request->file('imagen_portada')
                ->store('portadas', 'public');
            $imagenPortadaPath = $courseAttributes['imagen_portada'];
        }

        if ($request->boolean('auto_color')) {
            $courseAttributes['color_promedio'] = $this->averageCourseCoverColor->fromPublicPath($imagenPortadaPath);
        }

        $curso->update($courseAttributes);

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Curso $curso): RedirectResponse
    {
        $this->authorize('manage', $curso);

        $curso->load('modulos');

        $rutasArchivos = $curso->modulos
            ->pluck('ruta_archivo')
            ->filter()
            ->values();

        $rutaPortada = $curso->imagen_portada;

        $curso->delete();

        foreach ($rutasArchivos as $ruta) {
            Storage::disk('public')->delete($ruta);
        }

        if ($rutaPortada) {
            Storage::disk('public')->delete($rutaPortada);
        }

        return redirect()->route('capacitador.cursos.index')
            ->with('success', 'Curso eliminado correctamente.');
    }

    public function duplicar(Request $request, Curso $curso, DuplicateCourseAction $action): RedirectResponse
    {
        $this->authorize('manage', $curso);

        $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        $nuevoCurso = $action->execute($curso, $request->titulo);

        return redirect()->route('capacitador.cursos.show', $nuevoCurso)
            ->with('success', 'Nueva versión del curso creada exitosamente. Ahora puedes editarla.');
    }
}
