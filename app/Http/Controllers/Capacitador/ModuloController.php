<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Capacitador\StoreModuloRequest;
use App\Http\Requests\Capacitador\UpdateModuloRequest;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\MediaAsset;
use App\Models\Modulo;
use App\Services\History\EditHistoryService;
use App\Services\Media\ExternalVideoService;
use App\Services\Media\MediaAssetService;
use App\Services\Media\MediaAttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ModuloController extends Controller
{
    public function __construct(
        private readonly MediaAssetService $mediaAssets,
        private readonly MediaAttachmentService $mediaAttachments,
        private readonly ExternalVideoService $externalVideos,
        private readonly EditHistoryService $history,
    ) {}

    public function create(Curso $curso): View
    {
        $this->authorize('manage', $curso);

        return view('capacitador.modulos.crear', [
            'curso' => $curso,
            'tipos' => array_intersect_key(Modulo::TIPO_LABELS, array_flip(Modulo::CREATABLE_TIPOS)),
        ]);
    }

    public function store(StoreModuloRequest $request, Curso $curso): RedirectResponse
    {
        $moduleAttributes = $request->safe()->except(['ruta_archivo', 'media_asset_id', 'video_url']);
        $moduleAttributes['curso_id'] = $curso->id;
        $moduleAttributes['orden'] = ($curso->modulos()->max('orden') ?? 0) + 1;

        if (isset($moduleAttributes['contenido'])) {
            $moduleAttributes['contenido'] = clean($moduleAttributes['contenido']);
        }

        $asset = $this->requestedAsset($request, $moduleAttributes['tipo_contenido']);

        $modulo = $this->history->captureChange(
            $request->user(),
            EditHistoryService::CourseStructure,
            $curso->id,
            'Crear módulo',
            function () use ($moduleAttributes) {
                $modulo = Modulo::create($moduleAttributes);

                if ($modulo->tipo_contenido === 'evaluacion') {
                    Evaluacion::create(['modulo_id' => $modulo->id]);
                }

                return $modulo;
            },
        );
        if ($asset) {
            $this->mediaAttachments->request($asset, $modulo, 'content', auth()->user());
        }

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

        $moduleAttributes = $request->safe()->except(['ruta_archivo', 'media_asset_id', 'video_url']);

        if (isset($moduleAttributes['contenido'])) {
            $moduleAttributes['contenido'] = clean($moduleAttributes['contenido']);
        }

        $asset = $this->requestedAsset($request, $modulo->tipo_contenido);
        $this->history->captureChange(
            $request->user(),
            EditHistoryService::CourseStructure,
            $curso->id,
            'Editar módulo',
            fn () => $modulo->update($moduleAttributes),
        );
        if ($asset) {
            $this->mediaAttachments->request($asset, $modulo, 'content', auth()->user());
        }

        return redirect()->route('capacitador.cursos.show', $curso)
            ->with('success', 'Módulo actualizado correctamente.');
    }

    public function destroy(Curso $curso, Modulo $modulo): RedirectResponse
    {
        $this->authorize('manage', $curso);
        abort_unless($modulo->curso_id === $curso->id, 404);

        $orden = $modulo->orden;
        $this->history->captureChange(
            auth()->user(),
            EditHistoryService::CourseStructure,
            $curso->id,
            'Eliminar módulo',
            function () use ($curso, $modulo, $orden): void {
                $modulo->delete();

                $curso->modulos()
                    ->where('orden', '>', $orden)
                    ->orderBy('orden')
                    ->each(function (Modulo $item, int $index) use ($orden): void {
                        $item->update(['orden' => $orden + $index]);
                    });
            },
        );

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

        $this->history->captureChange(
            $request->user(),
            EditHistoryService::CourseStructure,
            $curso->id,
            'Reordenar módulos',
            function () use ($request, $curso): void {
                foreach ($request->input('orden') as $index => $moduloId) {
                    $curso->modulos()->where('id', $moduloId)->update(['orden' => $index + 1]);
                }
            },
        );

        return response()->json(['ok' => true]);
    }

    private function requestedAsset(StoreModuloRequest|UpdateModuloRequest $request, string $type): ?MediaAsset
    {
        if ($request->filled('media_asset_id')) {
            return MediaAsset::findOrFail($request->integer('media_asset_id'));
        }
        if ($type === 'video' && $request->filled('video_url')) {
            return $this->externalVideos->create($request->string('video_url')->toString(), auth()->user());
        }
        if (! $request->hasFile('ruta_archivo')) {
            return null;
        }
        $purpose = match ($type) {
            'video' => 'video', 'imagen' => 'image', 'pdf' => 'pdf', 'ppt', 'documento' => 'document',
            default => throw ValidationException::withMessages(['ruta_archivo' => 'Este tipo de módulo no admite archivos.']),
        };

        return $this->mediaAssets->ingestUploaded($request->file('ruta_archivo'), $purpose, auth()->user());
    }
}
