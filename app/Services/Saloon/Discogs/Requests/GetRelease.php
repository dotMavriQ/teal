<?php

declare(strict_types=1);

namespace App\Services\Saloon\Discogs\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetRelease extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int $releaseId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/releases/{$this->releaseId}";
    }
}
