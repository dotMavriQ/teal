<?php

declare(strict_types=1);

namespace App\Services\Saloon\OpenLibrary\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchBooks extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
        protected int $page = 1,
        protected int $limit = 20,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search.json';
    }

    protected function defaultQuery(): array
    {
        return [
            'q' => $this->searchQuery,
            'page' => $this->page,
            'limit' => $this->limit,
            'fields' => 'key,title,author_name,first_publish_year,cover_i,isbn,number_of_pages_median,publisher,edition_count',
        ];
    }
}
