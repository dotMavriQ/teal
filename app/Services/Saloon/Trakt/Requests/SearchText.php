<?php

declare(strict_types=1);

namespace App\Services\Saloon\Trakt\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchText extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
        protected string $type = 'movie,show',
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search/' . $this->type;
    }

    protected function defaultQuery(): array
    {
        return [
            'query' => $this->searchQuery,
            'extended' => 'full,images',
            'limit' => 5,
        ];
    }
}
