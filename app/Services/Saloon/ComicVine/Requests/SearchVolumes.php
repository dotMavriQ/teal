<?php

declare(strict_types=1);

namespace App\Services\Saloon\ComicVine\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchVolumes extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
        protected int $limit = 10,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search/';
    }

    protected function defaultQuery(): array
    {
        return [
            'resource_type' => 'volume',
            'query' => $this->searchQuery,
            'limit' => $this->limit,
            'field_list' => 'id,name,publisher,start_year,count_of_issues,image,description,site_detail_url',
        ];
    }
}
