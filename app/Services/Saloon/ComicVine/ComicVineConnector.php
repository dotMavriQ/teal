<?php

declare(strict_types=1);

namespace App\Services\Saloon\ComicVine;

use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\LaravelCacheStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

class ComicVineConnector extends Connector implements Cacheable
{
    use AcceptsJson;
    use HasCaching;
    use HasRateLimits;
    use HasTimeout;

    protected int $connectTimeout = 10;

    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return 'https://comicvine.gamespot.com/api';
    }

    protected function defaultHeaders(): array
    {
        return [
            'User-Agent' => 'TEAL Media Library/1.0',
            'Accept' => 'application/json',
        ];
    }

    protected function defaultQuery(): array
    {
        return [
            'api_key' => config('services.comic_vine.api_key'),
            'format' => 'json',
        ];
    }

    public function resolveCacheDriver(): \Saloon\CachePlugin\Contracts\Driver
    {
        return new LaravelCacheDriver(Cache::store());
    }

    public function cacheExpiryInSeconds(): int
    {
        return 86400; // Cache ComicVine data for 24 hours (it changes less frequently)
    }

    protected function resolveLimits(): array
    {
        // ComicVine API: 200 requests per resource per hour, pace at ~1 req/sec
        return [
            Limit::allow(200)->everyHour()->sleep(),
            Limit::allow(1)->everySeconds(1)->sleep(),
        ];
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return new LaravelCacheStore(Cache::store());
    }
}
