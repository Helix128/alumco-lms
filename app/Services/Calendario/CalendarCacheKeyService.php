<?php

namespace App\Services\Calendario;

use Illuminate\Support\Facades\Cache;

class CalendarCacheKeyService
{
    public function invalidate(): void
    {
        Cache::increment('calendar_cache_version');
    }

    /**
     * @param  array<string, int|string|null>  $segments
     */
    public function make(string $scope, array $segments): string
    {
        $version = (string) Cache::get('calendar_cache_version', 1);
        ksort($segments);

        return sprintf(
            'calendar:%s:v%s:%s',
            $scope,
            $version,
            md5(json_encode($segments, JSON_THROW_ON_ERROR))
        );
    }
}
