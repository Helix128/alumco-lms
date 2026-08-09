<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MediaAttachmentService
{
    public function request(MediaAsset $asset, Model $target, string $collection, User $actor): MediaAttachment
    {
        if ($asset->owner_id !== $actor->id && ! $actor->hasAdminAccess()) {
            throw ValidationException::withMessages(['media_asset_id' => 'El recurso no pertenece a tu sesión.']);
        }

        if ($asset->status === MediaAsset::STATUS_FAILED) {
            throw ValidationException::withMessages(['media_asset_id' => 'El recurso multimedia falló al procesarse.']);
        }

        $attachment = $target->mediaAttachments()->create([
            'media_asset_id' => $asset->id,
            'collection' => $collection,
            'active' => false,
        ]);

        if ($asset->status === MediaAsset::STATUS_READY) {
            $this->activate($attachment);
        }

        return $attachment->refresh();
    }

    public function activate(MediaAttachment $attachment): void
    {
        DB::transaction(function () use ($attachment): void {
            $attachment = MediaAttachment::query()->lockForUpdate()->findOrFail($attachment->id);
            if ($attachment->asset()->value('status') !== MediaAsset::STATUS_READY) {
                return;
            }

            $old = MediaAttachment::query()
                ->where('attachable_type', $attachment->attachable_type)
                ->where('attachable_id', $attachment->attachable_id)
                ->where('collection', $attachment->collection)
                ->where('active', true)
                ->whereKeyNot($attachment->id)
                ->lockForUpdate()
                ->get();

            $attachment->update(['active' => true, 'activated_at' => now()]);
            foreach ($old as $previous) {
                $assetId = $previous->media_asset_id;
                $previous->delete();
                $this->markUnreferencedIfNeeded($assetId);
            }

            $attachment->asset()->update(['unreferenced_at' => null]);
        });
    }

    public function activatePending(MediaAsset $asset): void
    {
        $asset->attachments()->where('active', false)->each(fn (MediaAttachment $attachment) => $this->activate($attachment));
    }

    public function detachAll(Model $target): void
    {
        $target->mediaAttachments()->get()->each(function (MediaAttachment $attachment): void {
            $assetId = $attachment->media_asset_id;
            $attachment->delete();
            $this->markUnreferencedIfNeeded($assetId);
        });
    }

    public function copy(Model $from, Model $to): void
    {
        $from->mediaAttachments()->where('active', true)->get()->each(function (MediaAttachment $attachment) use ($to): void {
            $to->mediaAttachments()->create([
                'media_asset_id' => $attachment->media_asset_id,
                'collection' => $attachment->collection,
                'active' => true,
                'activated_at' => now(),
            ]);
        });
    }

    private function markUnreferencedIfNeeded(int $assetId): void
    {
        if (! MediaAttachment::where('media_asset_id', $assetId)->exists()) {
            MediaAsset::whereKey($assetId)->update(['unreferenced_at' => now()]);
        }
    }
}
