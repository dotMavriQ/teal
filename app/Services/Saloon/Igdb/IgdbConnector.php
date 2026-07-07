<?php

declare(strict_types=1);

namespace App\Services\Saloon\Igdb;

use App\Support\ApiKey;
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
            'Client-ID' => ApiKey::resolve('igdb_client_id'),
            'Authorization' => 'Bearer '.$this->getAccessToken(),
        ];
    }

    protected function getAccessToken(): string
    {
        $clientId = ApiKey::resolve('igdb_client_id');
        $clientSecret = ApiKey::resolve('igdb_client_secret');

        if ($clientId === null || $clientSecret === null) {
            return '';
        }

        // Key the cached token by the credentials so a per-user client_id never
        // reuses another user's (or the instance's) Twitch token.
        $cacheKey = 'igdb_access_token:'.hash('sha256', $clientId.'|'.$clientSecret);

        $token = Cache::remember($cacheKey, 3600, function () use ($clientId, $clientSecret) {
            $response = Http::post('https://id.twitch.tv/oauth2/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials',
            ]);

            return $response->json('access_token');
        });

        return is_string($token) ? $token : '';
    }
}
