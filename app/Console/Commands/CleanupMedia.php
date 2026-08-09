<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\MediaUpload;
use App\Services\Media\MediaUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupMedia extends Command
{
    protected $signature = 'media:cleanup';

    protected $description = 'Limpia cargas vencidas y recursos sin referencia tras el período de gracia';

    public function handle(MediaUploadService $uploadService): int
    {
        $uploads = 0;
        MediaUpload::where('expires_at', '<', now())->eachById(function (MediaUpload $upload) use (&$uploads, $uploadService): void {
            if ($upload->temp_disk === 's3') {
                if ($upload->status !== 'completed') {
                    $uploadService->cancel($upload);
                }
            } else {
                Storage::disk($upload->temp_disk)->deleteDirectory($upload->temp_path);
            }
            $upload->delete();
            $uploads++;
        });

        MediaAttachment::where('active', false)
            ->where('created_at', '<=', now()->subDays(config('media.unreferenced_grace_days')))
            ->whereHas('asset', fn ($query) => $query->where('status', 'failed'))
            ->eachById(function (MediaAttachment $attachment): void {
                $assetId = $attachment->media_asset_id;
                $attachment->delete();
                if (! MediaAttachment::where('media_asset_id', $assetId)->exists()) {
                    MediaAsset::whereKey($assetId)->update(['unreferenced_at' => now()->subDays(config('media.unreferenced_grace_days'))]);
                }
            });

        $assets = 0;
        MediaAsset::doesntHave('attachments')
            ->where('unreferenced_at', '<=', now()->subDays(config('media.unreferenced_grace_days')))
            ->with('variants')
            ->eachById(function (MediaAsset $asset) use (&$assets): void {
                foreach ($asset->variants as $variant) {
                    Storage::disk($variant->disk)->delete($variant->path);
                }
                $asset->delete();
                $assets++;
            });

        $this->info("Sesiones vencidas: {$uploads}; recursos eliminados: {$assets}.");

        return self::SUCCESS;
    }
}
