<?php

declare(strict_types=1);

namespace App\Services\Saloon\Tmdb\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMovieDetails extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int $tmdbId,
        protected array $appendToResponse = ['credits'],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/movie/' . $this->tmdbId;
    }

    protected function defaultQuery(): array
    {
        return [
            'append_to_response' => implode(',', $this->appendToResponse),
        ];
    }
}
