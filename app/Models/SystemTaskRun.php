<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemTaskRun extends Model
{
    public const StatusRunning = 'running';

    public const StatusSuccess = 'success';

    public const StatusFailed = 'failed';

    protected $fillable = [
        'command',
        'status',
        'processed_count',
        'summary',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'processed_count' => 'integer',
        ];
    }

    public static function start(string $command): self
    {
        return self::create([
            'command' => $command,
            'status' => self::StatusRunning,
            'started_at' => now(),
        ]);
    }

    public function markSuccess(int $processedCount, ?string $summary = null): void
    {
        $this->update([
            'status' => self::StatusSuccess,
            'processed_count' => $processedCount,
            'summary' => $summary,
            'finished_at' => now(),
        ]);
    }

    public function markFailed(\Throwable $exception): void
    {
        $this->update([
            'status' => self::StatusFailed,
            'summary' => mb_substr($exception->getMessage(), 0, 1000),
            'finished_at' => now(),
        ]);
    }
}
