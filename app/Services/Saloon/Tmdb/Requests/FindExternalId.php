<?php

declare(strict_types=1);

namespace App\Services\Saloon\Tmdb\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class FindExternalId extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $externalId,
        protected string $source = 'imdb_id',
    ) {}

    public function resolveEndpoint(): string
    {
        return '/find/' . $this->externalId;
    }

    protected function defaultQuery(): array
    {
        return [
            'external_source' => $this->source,
        ];
    }
}
