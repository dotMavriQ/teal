<?php

declare(strict_types=1);

namespace App\Services\Saloon\SetlistFm\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchArtists extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search/artists';
    }

    protected function defaultQuery(): array
    {
        return [
            'artistName' => $this->searchQuery,
            'sort' => 'relevance',
        ];
    }
}
