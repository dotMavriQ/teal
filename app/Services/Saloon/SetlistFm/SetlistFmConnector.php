<?php

declare(strict_types=1);

namespace App\Services\Saloon\SetlistFm;

use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

class SetlistFmConnector extends Connector implements Cacheable
{
    use AcceptsJson;
    use HasTimeout;
    use HasCaching;

    protected int $connectTimeout = 10;
    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return 'https://api.setlist.fm/rest/1.0';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'x-api-key' => config('services.setlistfm.api_key', ''),
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
