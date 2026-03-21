<?php

declare(strict_types=1);

namespace App\Services\Saloon\Igdb;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\HasTimeout;

class IgdbConnector extends Connector
{
    use HasTimeout;

    protected int $connectTimeout = 10;
    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return 'https://api.igdb.com/v4';
    }

    public function defaultHeaders(): array
    {
        return [
            'Client-ID' => config('services.igdb.client_id'),
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ];
    }

    protected function getAccessToken(): string
    {
        return Cache::remember('igdb_access_token', 3600, function () {
            $response = Http::post('https://id.twitch.tv/oauth2/token', [
                'client_id' => config('services.igdb.client_id'),
                'client_secret' => config('services.igdb.client_secret'),
                'grant_type' => 'client_credentials',
            ]);

            return $response->json('access_token');
        });
    }
}
