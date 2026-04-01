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
        protected bool $exact = false,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search';
    }

    protected function defaultQuery(): array
    {
        $query = [
            'query' => $this->searchQuery,
            'type' => 'boardgame',
        ];

        if ($this->exact) {
            $query['exact'] = 1;
        }

        return $query;
    }
}
