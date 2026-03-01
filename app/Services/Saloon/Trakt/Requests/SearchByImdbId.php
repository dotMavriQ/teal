<?php

declare(strict_types=1);

namespace App\Services\Saloon\Trakt\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchByImdbId extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $imdbId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search/imdb/' . $this->imdbId;
    }

    protected function defaultQuery(): array
    {
        return [
            'extended' => 'full,images',
        ];
    }
}
