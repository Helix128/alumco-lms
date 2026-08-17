<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class MediaProcessor
{
    public function __construct(private readonly MediaValidationService $validation) {}

    public function process(MediaAsset $asset): void
    {
        $original = $asset->variant('original') ?? throw new RuntimeException('No existe la variante original.');
        [$validationPath, $cleanupValidation] = $this->localCopy($original, $asset->original_name);
        try {
            $metadata = $this->validation->inspect($asset->kind, $asset->original_name, $validationPath);
            $checksum = hash_file('sha256', $validationPath);
            $asset->update(['checksum' => $checksum, 'mime_type' => $metadata['mime'], 'metadata' => $metadata]);
            $original->update(['checksum' => $checksum, 'mime_type' => $metadata['mime'], 'metadata' => $metadata]);
        } finally {
            if ($cleanupValidation) {
                @unlink($validationPath);
            }
        }

        match ($asset->kind) {
            'cover' => $this->cover($asset->refresh(), $original->refresh()),
            'image' => $this->moduleImage($asset, $original),
            'video' => $this->video($asset, $original),
            'document' => $this->office($asset, $original),
            'pdf' => null,
            default => throw new RuntimeException('Tipo multimedia no soportado.'),
        };

        $asset->update([
            'status' => MediaAsset::STATUS_READY,
            'ready_at' => now(),
            'failed_at' => null,
            'processing_error' => null,
        ]);
    }

    private function cover(MediaAsset $asset, MediaVariant $original): void
    {
        $heroMax = (int) config('media.variants.cover_hero_max_side', 1440);
        $heroQuality = (int) config('media.variants.cover_hero_quality', 82);
        $thumbMax = (int) config('media.variants.cover_thumb_max_side', 480);
        $thumbQuality = (int) config('media.variants.cover_thumb_quality', 80);

        // 1. Variante principal en alta resolución (para vista detalle y hero)
        $this->imageWithVariant($asset, $original, 'optimized', $heroMax, $heroQuality);

        // 2. Variante thumbnail ligera (para catálogos y tarjetas)
        $this->imageWithVariant($asset, $original, 'thumbnail', $thumbMax, $thumbQuality);
    }

    private function moduleImage(MediaAsset $asset, MediaVariant $original): void
    {
        if (strtolower((string) data_get($original->metadata, 'extension', pathinfo($original->path, PATHINFO_EXTENSION))) === 'gif') {
            return;
        }
        $this->imageWithVariant($asset, $original, 'optimized', 2560, 85);
    }

    private function imageWithVariant(MediaAsset $asset, MediaVariant $original, string $variantName, int $maxSide, int $quality): void
    {
        if (! class_exists(\Imagick::class)) {
            throw new RuntimeException('Imagick no está disponible para optimizar imágenes.');
        }

        [$input, $cleanupInput] = $this->localCopy($original);
        $output = $this->temporaryPath('media-image-', 'webp');
        try {
            $image = new \Imagick($input);
            $image->autoOrientImage();
            $image->stripImage();
            if ($image->getImageWidth() > $maxSide || $image->getImageHeight() > $maxSide) {
                $image->thumbnailImage($maxSide, $maxSide, true, true);
            }
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality($quality);
            $image->writeImage($output);
            $image->clear();

            $this->storeVariant($asset, $variantName, $output, 'webp', 'image/webp', [
                'max_side' => $maxSide, 'quality' => $quality,
            ]);
        } finally {
            @unlink($output);
            if ($cleanupInput) {
                @unlink($input);
            }
        }
    }

    private function video(MediaAsset $asset, MediaVariant $original): void
    {
        [$input, $cleanupInput] = $this->localCopy($original);
        $output = $this->temporaryPath('media-video-', 'mp4');
        try {
            $probe = $this->probe($input);
            $video = collect($probe['streams'] ?? [])->firstWhere('codec_type', 'video');
            $audio = collect($probe['streams'] ?? [])->firstWhere('codec_type', 'audio');
            if (! $video || (float) data_get($probe, 'format.duration', 0) <= 0) {
                throw new RuntimeException('El video no contiene una pista legible o una duración válida.');
            }

            $compatible = data_get($video, 'codec_name') === 'h264'
                && ($audio === null || data_get($audio, 'codec_name') === 'aac')
                && (int) data_get($video, 'height', 0) <= 1080;

            $arguments = $compatible
                ? ['ffmpeg', '-y', '-v', 'error', '-i', $input, '-map', '0', '-c', 'copy', '-movflags', '+faststart', $output]
                : ['ffmpeg', '-y', '-v', 'error', '-i', $input, '-map', '0:v:0', '-map', '0:a:0?', '-vf', "scale='min(1920,iw)':'min(1080,ih)':force_original_aspect_ratio=decrease:force_divisible_by=2", '-c:v', 'libx264', '-preset', 'medium', '-crf', '23', '-threads', '2', '-c:a', 'aac', '-b:a', '128k', '-movflags', '+faststart', $output];

            $this->run($arguments, 3300);
            $verified = $this->probe($output);
            if (! collect($verified['streams'] ?? [])->contains('codec_type', 'video') || (float) data_get($verified, 'format.duration', 0) <= 0) {
                throw new RuntimeException('La variante de video generada no superó la verificación.');
            }

            $this->storeVariant($asset, 'optimized', $output, 'mp4', 'video/mp4', [
                'passthrough' => $compatible,
                'duration' => (float) data_get($verified, 'format.duration'),
            ]);

            // Extraer fotograma poster del video
            $this->extractVideoPoster($asset, $output);
        } finally {
            @unlink($output);
            if ($cleanupInput) {
                @unlink($input);
            }
        }
    }

    private function extractVideoPoster(MediaAsset $asset, string $videoPath): void
    {
        $posterPath = $this->temporaryPath('media-poster-', 'webp');
        try {
            $this->run([
                'ffmpeg', '-y', '-v', 'error',
                '-ss', '00:00:01',
                '-i', $videoPath,
                '-vframes', '1',
                '-vf', "scale='min(1280,iw)':'min(720,ih)':force_original_aspect_ratio=decrease",
                $posterPath,
            ], 60);

            if (file_exists($posterPath) && filesize($posterPath) > 0) {
                $this->storeVariant($asset, 'poster', $posterPath, 'webp', 'image/webp', [
                    'type' => 'video_poster',
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            @unlink($posterPath);
        }
    }

    private function office(MediaAsset $asset, MediaVariant $original): void
    {
        [$input, $cleanupInput] = $this->localCopy($original, $asset->original_name);
        $outputDir = sys_get_temp_dir().'/media-office-'.bin2hex(random_bytes(8));
        mkdir($outputDir, 0700, true);
        try {
            $this->run(['libreoffice', '--headless', '--nologo', '--nolockcheck', '--nodefault', '--convert-to', 'pdf', '--outdir', $outputDir, $input], 900);
            $files = glob($outputDir.'/*.pdf') ?: [];
            if (count($files) !== 1 || filesize($files[0]) < 5 || file_get_contents($files[0], false, null, 0, 5) !== '%PDF-') {
                throw new RuntimeException('LibreOffice no produjo una vista PDF válida.');
            }
            $this->storeVariant($asset, 'preview_pdf', $files[0], 'pdf', 'application/pdf');
        } finally {
            foreach (glob($outputDir.'/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($outputDir);
            if ($cleanupInput) {
                @unlink($input);
            }
        }
    }

    /** @return array<string,mixed> */
    private function probe(string $path): array
    {
        $process = $this->run(['ffprobe', '-v', 'error', '-show_streams', '-show_format', '-of', 'json', $path], 120);
        $decoded = json_decode($process->getOutput(), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('ffprobe devolvió una respuesta inválida.');
        }

        return $decoded;
    }

    private function run(array $command, int $timeout): Process
    {
        $process = new Process($command);
        $process->setTimeout($timeout);
        $process->mustRun();

        return $process;
    }

    /** @return array{string,bool} */
    private function localCopy(MediaVariant $variant, ?string $preferredName = null): array
    {
        $disk = Storage::disk($variant->disk);
        $localRoot = config("filesystems.disks.{$variant->disk}.root");
        if (is_string($localRoot)) {
            return [rtrim($localRoot, '/').'/'.$variant->path, false];
        }

        $suffix = pathinfo($preferredName ?? $variant->path, PATHINFO_EXTENSION);
        $path = $this->temporaryPath('media-input-', $suffix);
        $input = $disk->readStream($variant->path);
        $output = fopen($path, 'wb');
        stream_copy_to_stream($input, $output);
        fclose($input);
        fclose($output);

        return [$path, true];
    }

    /** @param array<string,mixed> $metadata */
    private function storeVariant(MediaAsset $asset, string $type, string $localPath, string $extension, string $mime, array $metadata = []): void
    {
        $diskName = config('media.disk');
        $path = 'assets/'.substr($asset->checksum, 0, 2)."/{$asset->id}/{$type}.{$extension}";
        $stream = fopen($localPath, 'rb');
        try {
            Storage::disk($diskName)->writeStream($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        MediaVariant::updateOrCreate(
            ['media_asset_id' => $asset->id, 'type' => $type],
            ['disk' => $diskName, 'path' => $path, 'mime_type' => $mime, 'size_bytes' => filesize($localPath), 'checksum' => hash_file('sha256', $localPath), 'metadata' => $metadata],
        );
    }

    private function temporaryPath(string $prefix, string $extension = ''): string
    {
        $base = tempnam(sys_get_temp_dir(), $prefix);
        if ($extension === '') {
            return $base;
        }
        @unlink($base);

        return $base.'.'.ltrim($extension, '.');
    }
}
