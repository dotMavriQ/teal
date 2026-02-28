<?php

declare(strict_types=1);

namespace App\Services\Saloon\Jikan\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchAnime extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $query,
        protected int $limit = 1,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/anime';
    }

    protected function defaultQuery(): array
    {
        return [
            'q' => $this->query,
            'limit' => $this->limit,
        ];
    }
}
