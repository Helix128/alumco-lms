<?php

namespace App\Services\Media;

use App\Models\MediaUpload;
use App\Models\User;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MediaUploadService
{
    public function __construct(
        private readonly MediaValidationService $validation,
        private readonly MediaCapacityService $capacity,
        private readonly MediaAssetService $assets,
    ) {}

    public function create(User $user, string $purpose, string $name, string $mime, int $size): MediaUpload
    {
        $this->capacity->ensureWritable();
        $this->validation->validateDeclaration($purpose, $name, $size);
        $chunkSize = (int) config('media.chunk_size');

        $upload = MediaUpload::create([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'status' => 'uploading',
            'original_name' => basename($name),
            'declared_mime_type' => $mime,
            'size_bytes' => $size,
            'chunk_size' => $chunkSize,
            'total_parts' => (int) ceil($size / $chunkSize),
            'received_parts' => [],
            'temp_disk' => config('media.temp_disk'),
            'temp_path' => '',
            'expires_at' => now()->addHours(config('media.upload_ttl_hours')),
        ]);
        $upload->update(['temp_path' => "uploads/{$upload->id}"]);

        if (config('media.disk') === 's3') {
            $path = "incoming/{$upload->id}/source";
            $result = $this->s3()->createMultipartUpload([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $path,
                'ContentType' => $mime ?: 'application/octet-stream',
            ]);
            $upload->update([
                'temp_disk' => 's3',
                'provider_path' => $path,
                'provider_upload_id' => (string) $result['UploadId'],
            ]);
        }

        return $upload->refresh();
    }

    /** @param resource $stream */
    public function acceptPart(MediaUpload $upload, int $number, $stream, int $length): MediaUpload
    {
        if ($upload->temp_disk === 's3') {
            throw ValidationException::withMessages(['upload' => 'Esta carga utiliza partes directas al proveedor.']);
        }
        $this->ensureOpen($upload);
        if ($number < 1 || $number > $upload->total_parts) {
            throw ValidationException::withMessages(['part' => 'Número de bloque inválido.']);
        }

        $expected = $number === $upload->total_parts
            ? $upload->size_bytes - (($number - 1) * $upload->chunk_size)
            : $upload->chunk_size;
        if ($length !== $expected || $length > $upload->chunk_size) {
            throw ValidationException::withMessages(['part' => 'El tamaño del bloque no coincide con la sesión.']);
        }

        $path = sprintf('%s/parts/%06d.part', $upload->temp_path, $number);
        Storage::disk($upload->temp_disk)->writeStream($path, $stream);
        $actualSize = Storage::disk($upload->temp_disk)->size($path);
        if ($actualSize !== $expected) {
            Storage::disk($upload->temp_disk)->delete($path);
            throw new RuntimeException('El bloque no pudo escribirse completamente.');
        }

        $parts = $upload->received_parts ?? [];
        $parts[(string) $number] = ['size' => $actualSize];
        ksort($parts, SORT_NUMERIC);
        $upload->update(['received_parts' => $parts]);

        return $upload->refresh();
    }

    public function complete(MediaUpload $upload): MediaUpload
    {
        $this->ensureOpen($upload);
        $upload = DB::transaction(function () use ($upload): MediaUpload {
            $locked = MediaUpload::query()->lockForUpdate()->findOrFail($upload->id);
            if ($locked->status === 'completed') {
                return $locked;
            }
            if (count($locked->received_parts ?? []) !== $locked->total_parts) {
                throw ValidationException::withMessages(['parts' => 'Aún faltan bloques por cargar.']);
            }
            $locked->update(['status' => 'assembling']);

            return $locked;
        });

        $disk = Storage::disk($upload->temp_disk);
        $partial = "{$upload->temp_path}/assembled.partial";
        $complete = "{$upload->temp_path}/assembled.complete";
        $output = fopen($disk->path($partial), 'wb');
        try {
            for ($part = 1; $part <= $upload->total_parts; $part++) {
                $path = sprintf('%s/parts/%06d.part', $upload->temp_path, $part);
                if (! $disk->exists($path)) {
                    throw new RuntimeException("Falta el bloque {$part}.");
                }
                $input = $disk->readStream($path);
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
        } finally {
            fclose($output);
        }

        if ($disk->size($partial) !== $upload->size_bytes) {
            $disk->delete($partial);
            $upload->update(['status' => 'uploading']);
            throw new RuntimeException('El archivo ensamblado no coincide con el tamaño declarado.');
        }
        rename($disk->path($partial), $disk->path($complete));

        try {
            $meta = $this->validation->inspect($upload->purpose, $upload->original_name, $disk->path($complete));
            $asset = $this->assets->ingestPath($disk->path($complete), $upload->original_name, $upload->purpose, $upload->user, $meta);
            $upload->update([
                'status' => 'completed',
                'media_asset_id' => $asset->id,
                'completed_at' => now(),
            ]);
            $disk->deleteDirectory($upload->temp_path);

            return $upload->refresh()->load('asset');
        } catch (\Throwable $exception) {
            $upload->update(['status' => 'failed']);
            throw $exception;
        }
    }

    /** @param list<array{PartNumber:int,ETag:string}> $parts */
    public function completeS3(MediaUpload $upload, array $parts): MediaUpload
    {
        $this->ensureOpen($upload);
        if ($upload->temp_disk !== 's3' || ! $upload->provider_upload_id || ! $upload->provider_path) {
            throw ValidationException::withMessages(['upload' => 'La sesión no es una carga S3/R2.']);
        }
        if (count($parts) !== $upload->total_parts) {
            throw ValidationException::withMessages(['parts' => 'La lista de partes está incompleta.']);
        }
        usort($parts, fn (array $a, array $b) => $a['PartNumber'] <=> $b['PartNumber']);
        foreach ($parts as $index => $part) {
            if ($part['PartNumber'] !== $index + 1 || $part['ETag'] === '') {
                throw ValidationException::withMessages(['parts' => 'La lista de partes no es válida.']);
            }
        }

        $this->s3()->completeMultipartUpload([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $upload->provider_path,
            'UploadId' => $upload->provider_upload_id,
            'MultipartUpload' => ['Parts' => $parts],
        ]);
        $asset = $this->assets->registerS3Object(
            $upload->provider_path,
            $upload->original_name,
            $upload->purpose,
            $upload->declared_mime_type ?? '',
            $upload->size_bytes,
            $upload->user,
        );
        $upload->update(['status' => 'completed', 'media_asset_id' => $asset->id, 'completed_at' => now()]);

        return $upload->refresh()->load('asset');
    }

    /** @return array{direct:bool,received_parts:list<int>,part_urls:array<int,string>,part_etags:array<int,string>} */
    public function clientState(MediaUpload $upload): array
    {
        if ($upload->temp_disk !== 's3') {
            return ['direct' => false, 'received_parts' => array_map('intval', array_keys($upload->received_parts ?? [])), 'part_urls' => [], 'part_etags' => []];
        }
        $listed = $this->s3()->listParts([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $upload->provider_path,
            'UploadId' => $upload->provider_upload_id,
        ]);
        $received = collect($listed['Parts'] ?? [])->mapWithKeys(fn ($part) => [(int) $part['PartNumber'] => (string) $part['ETag']])->all();
        $urls = [];
        for ($part = 1; $part <= $upload->total_parts; $part++) {
            if (isset($received[$part])) {
                continue;
            }
            $command = $this->s3()->getCommand('UploadPart', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $upload->provider_path,
                'UploadId' => $upload->provider_upload_id,
                'PartNumber' => $part,
            ]);
            $urls[$part] = (string) $this->s3()->createPresignedRequest($command, '+30 minutes')->getUri();
        }
        $upload->update(['received_parts' => $received]);

        return ['direct' => true, 'received_parts' => array_map('intval', array_keys($received)), 'part_urls' => $urls, 'part_etags' => $received];
    }

    public function cancel(MediaUpload $upload): void
    {
        if ($upload->status !== 'completed') {
            if ($upload->temp_disk === 's3' && $upload->provider_upload_id) {
                $this->s3()->abortMultipartUpload([
                    'Bucket' => config('filesystems.disks.s3.bucket'), 'Key' => $upload->provider_path, 'UploadId' => $upload->provider_upload_id,
                ]);
            } else {
                Storage::disk($upload->temp_disk)->deleteDirectory($upload->temp_path);
            }
            $upload->update(['status' => 'cancelled']);
        }
    }

    private function s3(): S3Client
    {
        return Storage::disk('s3')->getClient();
    }

    private function ensureOpen(MediaUpload $upload): void
    {
        if ($upload->expires_at->isPast()) {
            throw ValidationException::withMessages(['upload' => 'La sesión de carga expiró.']);
        }
        if (! in_array($upload->status, ['uploading', 'assembling'], true)) {
            throw ValidationException::withMessages(['upload' => 'La sesión de carga ya no está disponible.']);
        }
    }
}
