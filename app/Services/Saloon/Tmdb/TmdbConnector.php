<?php

declare(strict_types=1);

namespace App\Services\Saloon\Tmdb;

use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

class TmdbConnector extends Connector implements Cacheable
{
    use AcceptsJson;
    use HasTimeout;
    use HasCaching;

    protected int $connectTimeout = 10;
    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return 'https://api.themoviedb.org/3';
    }

    public function defaultHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($accessToken = config('services.tmdb.access_token')) {
            $headers['Authorization'] = 'Bearer ' . $accessToken;
        }

        return $headers;
    }

    public function defaultQuery(): array
    {
        $query = [];

        // If no bearer token, fall back to API key in query params
        if (!config('services.tmdb.access_token') && $apiKey = config('services.tmdb.api_key')) {
            $query['api_key'] = $apiKey;
        }

        return $query;
    }

    public function resolveCacheDriver(): \Saloon\CachePlugin\Contracts\Driver
    {
        return new LaravelCacheDriver(Cache::store());
    }

    public function cacheExpiryInSeconds(): int
    {
        return 3600; // Cache for 1 hour
    }
}
