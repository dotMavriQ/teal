<?php

declare(strict_types=1);

namespace App\Services\Saloon\Igdb\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasStringBody;

class GetGameDetails extends Request implements HasBody
{
    use HasStringBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected int $gameId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/games';
    }

    protected function defaultBody(): string
    {
        return 'where id = ' . $this->gameId . '; '
            . 'fields name,summary,cover.url,platforms.name,first_release_date,'
            . 'involved_companies.company.name,involved_companies.developer,'
            . 'involved_companies.publisher,genres.name,rating,total_rating,'
            . 'screenshots.url,websites.url,websites.category; '
            . 'limit 1;';
    }
}
