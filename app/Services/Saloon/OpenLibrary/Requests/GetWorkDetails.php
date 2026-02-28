<?php

declare(strict_types=1);

namespace App\Services\Saloon\OpenLibrary\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkDetails extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $workKey,
    ) {}

    public function resolveEndpoint(): string
    {
        // $workKey is expected to be something like "/works/OL123W"
        return $this->workKey . '.json';
    }
}
