<?php

namespace App\Http\Controllers\Capacitador;

use App\Actions\Cursos\DuplicateCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Capacitador\StoreCursoRequest;
use App\Http\Requests\Capacitador\UpdateCursoRequest;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\MediaAsset;
use App\Services\Analytics\LearningAnalyticsService;
use App\Services\Cursos\AverageCourseCoverColor;
use App\Services\Media\MediaAssetService;
use App\Services\Media\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CursoController extends Controller
{
    public function __construct(
        private readonly AverageCourseCoverColor $averageCourseCoverColor,
        private readonly MediaAssetService $mediaAssets,
        private readonly MediaAttachmentService $mediaAttachments,
    ) {}

    public function index(): View
    {
        $query = auth()->user()->hasAdminAccess()
            ? Curso::query()
            : auth()->user()->cursosImpartidos();

        $cursos = $query
            ->with(['capacitador', 'mediaAttachments.asset.variants'])
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
        $courseAttributes = $request->safe()->except(['imagen_portada', 'imagen_portada_asset_id', 'auto_color']);
        $courseAttributes['capacitador_id'] = auth()->id();
        $asset = $this->coverAsset($request);
        if ($request->boolean('auto_color')) {
            $courseAttributes['color_promedio'] = $this->averageCourseCoverColor->fromMediaAsset($asset);
        }
        $curso = Curso::create($courseAttributes);
        if ($asset) {
            $this->mediaAttachments->request($asset, $curso, 'cover', auth()->user());
        }

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
        $courseAttributes = $request->safe()->except(['imagen_portada', 'imagen_portada_asset_id', 'auto_color']);
        $asset = $this->coverAsset($request);
        if ($request->boolean('auto_color')) {
            $courseAttributes['color_promedio'] = $asset
                ? $this->averageCourseCoverColor->fromMediaAsset($asset)
                : $this->averageCourseCoverColor->fromPublicPath($curso->imagen_portada);
        }
        $curso->update($courseAttributes);
        if ($asset) {
            $this->mediaAttachments->request($asset, $curso, 'cover', auth()->user());
        }

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Curso $curso): RedirectResponse
    {
        $this->authorize('manage', $curso);

        $curso->load('modulos.mediaAttachments');
        foreach ($curso->modulos as $modulo) {
            $this->mediaAttachments->detachAll($modulo);
        }
        $this->mediaAttachments->detachAll($curso);
        $curso->delete();

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

    private function coverAsset(StoreCursoRequest|UpdateCursoRequest $request): ?MediaAsset
    {
        if ($request->filled('imagen_portada_asset_id')) {
            return MediaAsset::findOrFail($request->integer('imagen_portada_asset_id'));
        }
        if ($request->hasFile('imagen_portada')) {
            return $this->mediaAssets->ingestUploaded($request->file('imagen_portada'), 'cover', auth()->user());
        }

        return null;
    }
}
