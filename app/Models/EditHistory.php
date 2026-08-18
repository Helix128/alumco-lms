<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditHistory extends Model
{
    protected $fillable = [
        'user_id',
        'context',
        'scope_id',
        'label',
        'before_state',
        'after_state',
        'before_hash',
        'after_hash',
        'undone_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'before_state' => 'array',
            'after_state' => 'array',
            'undone_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
