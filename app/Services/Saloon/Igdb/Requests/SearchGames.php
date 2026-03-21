<?php

declare(strict_types=1);

namespace App\Services\Saloon\Igdb\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasStringBody;

class SearchGames extends Request implements HasBody
{
    use HasStringBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $searchQuery,
        protected int $limit = 20,
        protected int $offset = 0,
        protected ?int $platformId = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/games';
    }

    protected function defaultBody(): string
    {
        $escapedQuery = addslashes($this->searchQuery);

        $body = 'search "' . $escapedQuery . '"; '
            . 'fields name,summary,cover.url,platforms.name,first_release_date,'
            . 'involved_companies.company.name,involved_companies.developer,'
            . 'involved_companies.publisher,genres.name,rating,total_rating; ';

        if ($this->platformId !== null) {
            $body .= 'where platforms = (' . $this->platformId . '); ';
        }

        $body .= 'limit ' . $this->limit . '; '
            . 'offset ' . $this->offset . ';';

        return $body;
    }
}
