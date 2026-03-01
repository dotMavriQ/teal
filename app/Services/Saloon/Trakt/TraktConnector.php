<?php

declare(strict_types=1);

namespace App\Services\Saloon\Trakt;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

class TraktConnector extends Connector
{
    use AcceptsJson;
    use HasTimeout;

    protected int $connectTimeout = 10;
    protected int $requestTimeout = 30;

    public function resolveBaseUrl(): string
    {
        return 'https://api.trakt.tv';
    }

    public function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'trakt-api-version' => '2',
            'trakt-api-key' => config('services.trakt.client_id'),
        ];
    }
}
