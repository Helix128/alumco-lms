<?php

namespace App\Services\Media;

use App\Jobs\ProcessMediaAsset;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaAssetService
{
    public function __construct(
        private readonly MediaValidationService $validation,
        private readonly MediaCapacityService $capacity,
    ) {}

    public function ingestUploaded(UploadedFile $file, string $purpose, User $owner): MediaAsset
    {
        $this->capacity->ensureWritable();
        $path = $file->getRealPath();
        $meta = $this->validation->inspect($purpose, $file->getClientOriginalName(), $path);

        return $this->ingestPath($path, $file->getClientOriginalName(), $purpose, $owner, $meta);
    }

    /** @param array<string,mixed>|null $meta */
    public function ingestPath(string $localPath, string $name, string $purpose, User $owner, ?array $meta = null): MediaAsset
    {
        $this->capacity->ensureWritable();
        $meta ??= $this->validation->inspect($purpose, $name, $localPath);
        $purpose = $this->validation->normalizePurpose($purpose, $name);
        $checksum = hash_file('sha256', $localPath);
        $profile = $this->profile($purpose, $meta['extension']);

        $existing = MediaAsset::query()
            ->where('owner_id', $owner->id)
            ->where('checksum', $checksum)
            ->where('transformation_profile', $profile)
            ->where('status', MediaAsset::STATUS_READY)
            ->first();
        if ($existing) {
            return $existing;
        }

        $asset = DB::transaction(fn () => MediaAsset::create([
            'owner_id' => $owner->id,
            'kind' => $purpose,
            'status' => MediaAsset::STATUS_PROCESSING,
            'source' => 'upload',
            'original_name' => Str::limit(basename($name), 255, ''),
            'mime_type' => $meta['mime'],
            'size_bytes' => filesize($localPath),
            'checksum' => $checksum,
            'transformation_profile' => $profile,
            'metadata' => $meta,
            'unreferenced_at' => now(),
        ]));

        $extension = $meta['extension'] ?: 'bin';
        $storagePath = 'assets/'.substr($checksum, 0, 2)."/{$asset->id}/original.{$extension}";
        $stream = fopen($localPath, 'rb');
        try {
            Storage::disk(config('media.disk'))->writeStream($storagePath, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        MediaVariant::create([
            'media_asset_id' => $asset->id,
            'type' => 'original',
            'disk' => config('media.disk'),
            'path' => $storagePath,
            'mime_type' => $meta['mime'],
            'size_bytes' => filesize($localPath),
            'checksum' => $checksum,
            'metadata' => $meta,
        ]);

        ProcessMediaAsset::dispatch($asset->id)->onQueue('media');

        return $asset->refresh();
    }

    public function registerS3Object(string $path, string $name, string $purpose, string $declaredMime, int $size, User $owner): MediaAsset
    {
        $purpose = $this->validation->normalizePurpose($purpose, $name);
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $placeholder = hash('sha256', "s3:{$path}");
        $asset = MediaAsset::create([
            'owner_id' => $owner->id,
            'kind' => $purpose,
            'status' => MediaAsset::STATUS_PROCESSING,
            'source' => 'upload',
            'original_name' => Str::limit(basename($name), 255, ''),
            'mime_type' => $declaredMime ?: 'application/octet-stream',
            'size_bytes' => $size,
            'checksum' => $placeholder,
            'transformation_profile' => $this->profile($purpose, $extension),
            'metadata' => ['extension' => $extension, 'validation' => 'pending'],
            'unreferenced_at' => now(),
        ]);
        MediaVariant::create([
            'media_asset_id' => $asset->id,
            'type' => 'original',
            'disk' => 's3',
            'path' => $path,
            'mime_type' => $asset->mime_type,
            'size_bytes' => $size,
            'checksum' => $placeholder,
            'metadata' => $asset->metadata,
        ]);
        ProcessMediaAsset::dispatch($asset->id)->onQueue('media');

        return $asset;
    }

    private function profile(string $purpose, string $extension): string
    {
        return match ($purpose) {
            'cover' => 'cover-webp-1600-q82-v1',
            'image' => $extension === 'gif' ? 'gif-original-v1' : 'image-webp-2560-q85-v1',
            'video' => 'video-h264-aac-1080-crf23-v1',
            'document' => 'office-pdf-v1',
            default => 'original-v1',
        };
    }
}
