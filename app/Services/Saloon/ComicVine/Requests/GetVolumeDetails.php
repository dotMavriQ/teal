<?php

declare(strict_types=1);

namespace App\Services\Saloon\ComicVine\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetVolumeDetails extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $volumeId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/volume/4050-' . $this->volumeId . '/';
    }

    protected function defaultQuery(): array
    {
        return [
            'field_list' => 'id,name,publisher,start_year,count_of_issues,image,description,site_detail_url,people,characters',
        ];
    }
}
