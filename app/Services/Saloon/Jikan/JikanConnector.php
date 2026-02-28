<?php

declare(strict_types=1);

namespace App\Services\Saloon\Jikan;

use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

class JikanConnector extends Connector implements Cacheable
{
    use AcceptsJson;
    use HasTimeout;
    use HasCaching;

    protected int $connectTimeout = 10;
    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return 'https://api.jikan.moe/v4';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    public function resolveCacheDriver(): \Saloon\CachePlugin\Contracts\Driver
    {
        return new LaravelCacheDriver(Cache::store());
    }

    public function cacheExpiryInSeconds(): int
    {
        return 86400; // Cache Anime data for 24 hours
    }
}
