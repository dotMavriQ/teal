<?php

declare(strict_types=1);

namespace App\Services\Saloon\ComicVine\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetIssues extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $volumeId,
        protected int $offset = 0,
        protected int $limit = 100,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/issues/';
    }

    protected function defaultQuery(): array
    {
        return [
            'filter' => 'volume:' . $this->volumeId,
            'sort' => 'issue_number:asc',
            'offset' => $this->offset,
            'limit' => $this->limit,
            'field_list' => 'id,name,issue_number,cover_date,image,description,site_detail_url',
        ];
    }
}
