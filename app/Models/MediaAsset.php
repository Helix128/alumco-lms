<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaAsset extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_READY = 'ready';

    public const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size_bytes' => 'integer',
            'ready_at' => 'datetime',
            'failed_at' => 'datetime',
            'unreferenced_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    public function variant(string $type): ?MediaVariant
    {
        return $this->relationLoaded('variants')
            ? $this->variants->firstWhere('type', $type)
            : $this->variants()->where('type', $type)->first();
    }
}
