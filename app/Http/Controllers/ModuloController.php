<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Modulo;
use App\Models\ProgresoModulo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ModuloController extends Controller
{
    private const INLINE_MIME_TYPES = [
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'mp4' => 'video/mp4',
        'ogg' => 'video/ogg',
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
    ];

    public function verArchivo(Curso $curso, Modulo $modulo): StreamedResponse
    {
        $this->authorizeFileAccess($curso, $modulo);
        $this->ensureFileExists($modulo);

        return Storage::disk('public')->response(
            $modulo->ruta_archivo,
            $this->displayFileName($modulo),
            $this->inlineHeadersFor($modulo)
        );
    }

    public function descargarArchivo(Curso $curso, Modulo $modulo): StreamedResponse
    {
        $this->authorizeFileAccess($curso, $modulo);
        $this->ensureFileExists($modulo);

        $nombreDownload = $modulo->nombre_archivo_original ?? basename($modulo->ruta_archivo);

        return Storage::disk('public')->download($modulo->ruta_archivo, $nombreDownload);
    }

    private function authorizeFileAccess(Curso $curso, Modulo $modulo): void
    {
        abort_if($modulo->curso_id !== $curso->id, 404);

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        // 1. Admin / Dev tienen acceso total
        if ($user->hasAdminAccess()) {
            return;
        }

        // 2. El capacitador autor del curso tiene acceso
        if ($curso->capacitador_id === $user->id) {
            return;
        }

        // 3. Trabajadores con el curso asignado
        if ($this->belongsToUserEstamento($curso, $user)) {
            // Nota: No validamos estaAccesiblePara() o estaDisponiblePara() aquí
            // para permitir descargas si el usuario ya llegó a la vista del módulo.
            return;
        }

        abort(403, 'No tienes permisos para acceder a este archivo.');
    }

    public function show(Curso $curso, Modulo $modulo): View|RedirectResponse
    {
        abort_if($modulo->curso_id !== $curso->id, 404);

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $this->authorizeCourseAccess($curso, $user);
        $this->loadCourseModulesFor($curso, $user);

        // Sincronizar el módulo actual con el de la colección cargada (relaciones incluidas)
        $moduloCargado = $curso->modulos->find($modulo->id);
        abort_if(! $moduloCargado, 404);

        abort_unless(
            $moduloCargado->estaAccesiblePara($user, $curso),
            403,
            'Este módulo aún está bloqueado. Completa los módulos anteriores primero.'
        );

        $progreso = $curso->progresoParaUsuario($user);
        $totalModulos = $curso->modulos->count();
        $moduloActual = $curso->modulos->search(fn ($m) => $m->id === $modulo->id) + 1;
        $siguiente = $curso->modulos->where('orden', '>', $modulo->orden)->first();

        if ($moduloCargado->tipo_contenido === 'evaluacion') {
            $moduloCargado->load('evaluacion.preguntas.opciones');

            if (! $moduloCargado->evaluacion) {
                return redirect()->route('cursos.show', $curso)
                    ->with('error', 'Esta evaluación no está disponible todav&iacute;a.');
            }

            return view('modulos.evaluacion', [
                'curso' => $curso,
                'modulo' => $moduloCargado,
                'progreso' => $progreso,
            ]);
        }

        return view('modulos.capsula', [
            'curso' => $curso,
            'modulo' => $moduloCargado,
            'progreso' => $progreso,
            'moduloActual' => $moduloActual,
            'totalModulos' => $totalModulos,
            'siguiente' => $siguiente,
        ]);
    }

    public function completar(Curso $curso, Modulo $modulo): RedirectResponse
    {
        $modulo = $this->authorizeModuloAccess($curso, $modulo);

        ProgresoModulo::updateOrCreate(
            ['user_id' => auth()->id(), 'modulo_id' => $modulo->id],
            ['completado' => true, 'fecha_completado' => now()]
        );

        $action = request()->input('action', 'next');

        if ($action === 'course') {
            return redirect()->route('cursos.show', $curso)
                ->with('success', 'Módulo completado.');
        }

        // Cargar módulos para encontrar el siguiente
        $siguiente = $curso->modulos()->where('orden', '>', $modulo->orden)->orderBy('orden')->first();

        if ($siguiente) {
            return redirect()->route('modulos.show', [$curso, $siguiente]);
        }

        return redirect()->route('cursos.show', $curso)
            ->with('success', '¡Curso completado!');
    }

    private function authorizeModuloAccess(Curso $curso, Modulo $modulo): Modulo
    {
        abort_if($modulo->curso_id !== $curso->id, 404);

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $this->authorizeCourseAccess($curso, $user);
        $this->loadCourseModulesFor($curso, $user);

        $moduloCargado = $curso->modulos->find($modulo->id);
        abort_if(! $moduloCargado, 404);

        abort_unless(
            $moduloCargado->estaAccesiblePara($user, $curso),
            403,
            'Este módulo aún está bloqueado. Completa los módulos anteriores primero.'
        );

        return $moduloCargado;
    }

    private function authorizeCourseAccess(Curso $curso, User $user): void
    {
        $isPreview = session('preview_mode', false);
        $esAutorOAdmin = $user->hasAdminAccess() || $curso->capacitador_id === $user->id;
        $estaAsociadoPorEstamento = $this->belongsToUserEstamento($curso, $user);

        if ($isPreview && ($esAutorOAdmin || $estaAsociadoPorEstamento)) {
            return;
        }

        abort_unless($curso->estaDisponiblePara($user), 403, 'Este curso no tiene un periodo de disponibilidad activo.');
        abort_unless($estaAsociadoPorEstamento, 403, 'No tienes acceso a este curso.');
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

    private function loadCourseModulesFor(Curso $curso, User $user): void
    {
        $curso->load(['modulos' => function ($query) use ($user) {
            $query->orderBy('orden')
                ->with(['progresos' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
        }]);
    }

    private function ensureFileExists(Modulo $modulo): void
    {
        abort_unless($modulo->ruta_archivo, 404);
        abort_unless(Storage::disk('public')->exists($modulo->ruta_archivo), 404, 'Archivo no encontrado.');
    }

    private function displayFileName(Modulo $modulo): string
    {
        return $modulo->nombre_archivo_original ?? basename($modulo->ruta_archivo);
    }

    /**
     * @return array<string, string>
     */
    private function inlineHeadersFor(Modulo $modulo): array
    {
        $fileName = $this->displayFileName($modulo);
        $fallbackFileName = Str::ascii($fileName) ?: 'archivo';

        return [
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $fileName,
                $fallbackFileName,
            ),
            'Content-Type' => $this->mimeTypeFor($modulo),
            'X-Content-Type-Options' => 'nosniff',
        ];
    }

    private function mimeTypeFor(Modulo $modulo): string
    {
        $extension = strtolower(pathinfo($this->displayFileName($modulo), PATHINFO_EXTENSION));

        if ($extension === '') {
            $extension = strtolower(pathinfo($modulo->ruta_archivo, PATHINFO_EXTENSION));
        }

        return self::INLINE_MIME_TYPES[$extension] ?? 'application/octet-stream';
    }
}
