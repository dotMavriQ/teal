<?php

declare(strict_types=1);

namespace App\Services\Saloon\Tmdb\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchMulti extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
        protected int $page = 1,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search/multi';
    }

    protected function defaultQuery(): array
    {
        return [
            'query' => $this->searchQuery,
            'page' => $this->page,
        ];
    }
}
