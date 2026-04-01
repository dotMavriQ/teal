<?php

declare(strict_types=1);

namespace App\Services\Saloon\Bgg\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetBoardGameDetails extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int $bggId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/thing';
    }

    protected function defaultQuery(): array
    {
        return [
            'id' => $this->bggId,
            'stats' => 1,
        ];
    }
}
