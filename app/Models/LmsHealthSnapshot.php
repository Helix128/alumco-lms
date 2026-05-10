<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsHealthSnapshot extends Model
{
    protected $fillable = [
        'failed_jobs_count',
        'pending_jobs_count',
        'error_rate',
        'active_users',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'failed_jobs_count' => 'integer',
            'pending_jobs_count' => 'integer',
            'error_rate' => 'decimal:2',
            'active_users' => 'integer',
            'captured_at' => 'datetime',
        ];
    }
}
