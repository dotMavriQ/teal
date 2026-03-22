<?php

declare(strict_types=1);

namespace App\Services\Saloon\Bgg\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchBoardGames extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search';
    }

    protected function defaultQuery(): array
    {
        return [
            'query' => $this->searchQuery,
            'type' => 'boardgame',
        ];
    }
}
