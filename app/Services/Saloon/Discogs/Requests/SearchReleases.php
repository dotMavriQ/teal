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
    ) {}

    public function resolveEndpoint(): string
    {
        return '/database/search';
    }

    protected function defaultQuery(): array
    {
        return [
            'q' => $this->searchQuery,
            'type' => $this->type,
            'per_page' => 20,
        ];
    }
}
