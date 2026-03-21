<?php

declare(strict_types=1);

namespace App\Services\Saloon\GoogleBooks\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchVolumes extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
        protected int $startIndex = 0,
        protected int $maxResults = 20,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/volumes';
    }

    protected function defaultQuery(): array
    {
        return [
            'q' => $this->searchQuery,
            'startIndex' => $this->startIndex,
            'maxResults' => $this->maxResults,
        ];
    }
}
