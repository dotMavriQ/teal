<?php

declare(strict_types=1);

namespace App\Services\Saloon\SetlistFm\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetArtistSetlists extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $mbid,
        protected int $page = 1,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/artist/{$this->mbid}/setlists";
    }

    protected function defaultQuery(): array
    {
        return [
            'p' => $this->page,
        ];
    }
}
