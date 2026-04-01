<?php

declare(strict_types=1);

namespace App\Services\Saloon\Discogs\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchReleases extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
        protected ?string $type = 'master',
        protected bool $artistMode = false,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/database/search';
    }

    protected function defaultQuery(): array
    {
        $query = [
            'type' => $this->type,
            'per_page' => 20,
        ];

        if ($this->artistMode) {
            $query['artist'] = $this->searchQuery;
            $query['sort'] = 'have';
            $query['sort_order'] = 'desc';
        } else {
            $query['q'] = $this->searchQuery;
        }

        return $query;
    }
}
