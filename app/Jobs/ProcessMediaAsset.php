<?php

namespace App\Jobs;

use App\Models\MediaAsset;
use App\Services\Media\MediaAttachmentService;
use App\Services\Media\MediaProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessMediaAsset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 3600;

    public bool $failOnTimeout = true;

    public function __construct(public readonly int $assetId) {}

    public function middleware(): array
    {
        return [(new WithoutOverlapping("media-asset-{$this->assetId}"))->expireAfter(3700)];
    }

    public function handle(MediaProcessor $processor, MediaAttachmentService $attachments): void
    {
        $asset = MediaAsset::with('variants')->findOrFail($this->assetId);
        if ($asset->status === MediaAsset::STATUS_READY) {
            $attachments->activatePending($asset);

            return;
        }

        $processor->process($asset);
        $attachments->activatePending($asset->refresh());
    }

    public function failed(?Throwable $exception): void
    {
        MediaAsset::whereKey($this->assetId)->update([
            'status' => MediaAsset::STATUS_FAILED,
            'failed_at' => now(),
            'processing_error' => mb_substr($exception?->getMessage() ?? 'Error de procesamiento desconocido.', 0, 2000),
        ]);
    }
}
