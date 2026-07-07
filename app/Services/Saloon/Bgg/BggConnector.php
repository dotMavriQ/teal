<?php

declare(strict_types=1);

namespace App\Services\Saloon\Bgg;

use App\Support\ApiKey;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\HasTimeout;

class BggConnector extends Connector
{
    use HasTimeout;

    protected int $connectTimeout = 10;

    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return 'https://boardgamegeek.com/xmlapi2';
    }

    public function defaultHeaders(): array
    {
        $token = ApiKey::resolve('bgg_api_token');

        if (is_string($token) && $token !== '') {
            return [
                'Authorization' => 'Bearer '.$token,
            ];
        }

        return [];
    }
}
