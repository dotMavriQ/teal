<?php

declare(strict_types=1);

namespace App\Services\Saloon\Discogs;

use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

class DiscogsConnector extends Connector implements Cacheable
{
    use AcceptsJson;
    use HasTimeout;
    use HasCaching;

    protected int $connectTimeout = 10;
    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return 'https://api.discogs.com';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'User-Agent' => 'TEAL/1.0 +https://github.com/dotMavriQ/teal',
            'Authorization' => 'Discogs token=' . config('services.discogs.token', ''),
        ];
    }

    public function resolveCacheDriver(): \Saloon\CachePlugin\Contracts\Driver
    {
        return new LaravelCacheDriver(Cache::store());
    }

    public function cacheExpiryInSeconds(): int
    {
        return 604800; // 7 days
    }
}
