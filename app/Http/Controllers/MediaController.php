<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\Modulo;
use App\Models\User;
use App\Services\Cursos\CourseAccessService;
use App\Services\Cursos\ModuleAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MediaController extends Controller
{
    public function __construct(
        private readonly ModuleAccessService $moduleAccess,
        private readonly CourseAccessService $courseAccess,
    ) {}

    public function show(MediaAsset $asset, string $variant = 'display'): Response|StreamedResponse|RedirectResponse
    {
        $this->authorizeAsset($asset);
        abort_unless($asset->status === MediaAsset::STATUS_READY, 409, 'El recurso aún se está procesando.');
        $media = $this->resolveVariant($asset->loadMissing('variants'), $variant);
        abort_unless($media && Storage::disk($media->disk)->exists($media->path), 404, 'Archivo no encontrado.');

        $download = $variant === 'original-download';
        $name = $download ? $asset->original_name : $this->displayName($asset, $media->mime_type);
        $disposition = HeaderUtils::makeDisposition($download ? 'attachment' : 'inline', $name, Str::ascii($name) ?: 'archivo');
        $headers = [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => $disposition,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600, immutable',
        ];

        if ($media->disk === 'local_media' && app()->environment('production')) {
            $internal = rtrim(config('media.local_internal_prefix'), '/').'/'.ltrim($media->path, '/');

            return response('', 200, [...$headers, 'X-Accel-Redirect' => $internal]);
        }
        if ($media->disk === 's3') {
            $url = Storage::disk('s3')->temporaryUrl($media->path, now()->addMinutes(config('media.temporary_url_minutes')), [
                'ResponseContentType' => $media->mime_type,
                'ResponseContentDisposition' => $disposition,
            ]);

            return redirect()->away($url);
        }

        return Storage::disk($media->disk)->response($media->path, $name, $headers);
    }

    public function download(MediaAsset $asset): Response|StreamedResponse|RedirectResponse
    {
        return $this->show($asset, 'original-download');
    }

    public function legacyCover(Curso $curso): StreamedResponse
    {
        abort_unless($this->canViewCourseCover($curso), 403);
        abort_unless($curso->imagen_portada && Storage::disk('public')->exists($curso->imagen_portada), 404, 'Portada legada no encontrada.');

        return Storage::disk('public')->response($curso->imagen_portada, basename($curso->imagen_portada), [
            'Cache-Control' => 'private, max-age=300', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeAsset(MediaAsset $asset): void
    {
        $attachment = MediaAttachment::with('attachable')
            ->where('media_asset_id', $asset->id)
            ->where('active', true)
            ->get()
            ->first(function (MediaAttachment $attachment): bool {
                $target = $attachment->attachable;
                if ($target instanceof Curso) {
                    return $this->canViewCourseCover($target);
                }
                if ($target instanceof Modulo) {
                    try {
                        $this->moduleAccess->authorizeAccess($target->curso, $target, $this->user());

                        return true;
                    } catch (HttpException) {
                        return false;
                    }
                }

                return false;
            });
        abort_unless($attachment, 403);
    }

    private function resolveVariant(MediaAsset $asset, string $requested)
    {
        if (in_array($requested, ['original', 'original-download'], true)) {
            return $asset->variant('original');
        }

        return match ($asset->kind) {
            'cover', 'image', 'video' => $asset->variant('optimized') ?? $asset->variant('original'),
            'document' => $asset->variant('preview_pdf') ?? $asset->variant('original'),
            default => $asset->variant('original'),
        };
    }

    private function displayName(MediaAsset $asset, string $mime): string
    {
        return $asset->kind === 'document' && $mime === 'application/pdf'
            ? pathinfo($asset->original_name, PATHINFO_FILENAME).'.pdf'
            : $asset->original_name;
    }

    private function user(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function canViewCourseCover(Curso $course): bool
    {
        $user = $this->user();
        if ($this->courseAccess->canViewAsWorker($user, $course)) {
            return true;
        }

        return $user->estamento_id
            && $course->estamentos()->where('estamentos.id', $user->estamento_id)->exists();
    }
}
