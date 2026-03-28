<?php

declare(strict_types=1);

namespace App\Services\Saloon\SetlistFm\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchSetlists extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $artistName,
        protected ?string $cityName = null,
        protected ?string $year = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/search/setlists';
    }

    protected function defaultQuery(): array
    {
        $params = [
            'artistName' => $this->artistName,
        ];

        if ($this->cityName) {
            $params['cityName'] = $this->cityName;
        }

        if ($this->year) {
            $params['year'] = $this->year;
        }

        return $params;
    }
}
