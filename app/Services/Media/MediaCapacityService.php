<?php

namespace App\Services\Media;

use RuntimeException;

class MediaCapacityService
{
    /** @return array{used:int,total:int,free:int,percent:float,warning:bool,blocked:bool} */
    public function status(): array
    {
        $root = config('filesystems.disks.local_media.root');
        if (config('media.disk') !== 'local_media' || ! is_string($root)) {
            return ['used' => 0, 'total' => 0, 'free' => PHP_INT_MAX, 'percent' => 0.0, 'warning' => false, 'blocked' => false];
        }

        if (! is_dir($root)) {
            mkdir($root, 0775, true);
        }
        $total = (int) disk_total_space($root);
        $free = (int) disk_free_space($root);
        $used = max(0, $total - $free);
        $percent = $total > 0 ? ($used / $total) * 100 : 0.0;
        $blocked = $percent >= config('media.capacity.block_percent')
            || $free < config('media.capacity.minimum_free_bytes');

        return [
            'used' => $used, 'total' => $total, 'free' => $free, 'percent' => $percent,
            'warning' => $percent >= config('media.capacity.warn_percent'), 'blocked' => $blocked,
        ];
    }

    public function ensureWritable(): void
    {
        if ($this->status()['blocked']) {
            throw new RuntimeException('No hay espacio suficiente para nuevas cargas multimedia. Contacta a soporte.');
        }
    }
}
