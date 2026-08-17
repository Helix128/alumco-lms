<?php

namespace App\Services\Media;

use App\Models\Curso;
use App\Models\Modulo;
use Illuminate\Support\Facades\Storage;

class MediaUrlResolver
{
    public function courseCover(Curso $curso, string $variant = 'optimized'): ?string
    {
        $asset = $curso->coverMedia();
        if ($asset) {
            return route('media.show', [$asset, $variant]);
        }

        return $curso->imagen_portada && Storage::disk('public')->exists($curso->imagen_portada)
            ? route('media.legacy-cover', $curso)
            : null;
    }

    public function courseCoverThumbnail(Curso $curso): ?string
    {
        return $this->courseCover($curso, 'thumbnail');
    }

    public function videoPoster(Modulo $modulo): ?string
    {
        $asset = $modulo->contentMedia();
        if ($asset && $asset->variant('poster')) {
            return route('media.show', [$asset, 'poster']);
        }

        return null;
    }

    public function module(Modulo $modulo, bool $download = false): ?string
    {
        $asset = $modulo->contentMedia();
        if ($asset) {
            return $download
                ? route('media.download', $asset)
                : route('media.show', [$asset, 'display']);
        }
        if (! $modulo->ruta_archivo) {
            return null;
        }

        return route($download ? 'modulos.descargar' : 'modulos.archivo', [$modulo->curso_id, $modulo->id]);
    }
}
