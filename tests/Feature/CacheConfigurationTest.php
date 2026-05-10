<?php

namespace Tests\Feature;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class CacheConfigurationTest extends TestCase
{
    public function test_failover_store_prioritizes_redis_cache(): void
    {
        $this->assertSame('failover', config('cache.stores.failover.driver'));
        $this->assertSame(['redis', 'database', 'array'], config('cache.stores.failover.stores'));
        $this->assertSame('cache', config('cache.stores.redis.connection'));
        $this->assertSame('default', config('cache.stores.redis.lock_connection'));
    }

    public function test_failover_store_continues_when_primary_store_fails(): void
    {
        Cache::extend('always-fails', fn (): Repository => new Repository(new class implements Store
        {
            public function get($key): mixed
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function many(array $keys): array
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function put($key, $value, $seconds): bool
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function putMany(array $values, $seconds): bool
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function increment($key, $value = 1): int|bool
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function decrement($key, $value = 1): int|bool
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function forever($key, $value): bool
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function touch($key, $seconds): bool
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function forget($key): bool
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function flush(): bool
            {
                throw new RuntimeException('Cache store unavailable.');
            }

            public function getPrefix(): string
            {
                return '';
            }
        }));

        config([
            'cache.stores.always-fails' => ['driver' => 'always-fails'],
            'cache.stores.failover-test' => [
                'driver' => 'failover',
                'stores' => ['always-fails', 'array'],
            ],
        ]);

        Cache::forgetDriver(['always-fails', 'array', 'failover-test']);

        $cache = Cache::store('failover-test');

        $this->assertTrue($cache->put('cache-fallback-check', 'ok', 60));
        $this->assertSame('ok', $cache->get('cache-fallback-check'));
    }
}
