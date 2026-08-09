<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaVariant extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'size_bytes' => 'integer'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }
}
