<?php

declare(strict_types=1);

namespace App\Services\Saloon\Jikan\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAnimeDetails extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int $malId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/anime/' . $this->malId;
    }
}
