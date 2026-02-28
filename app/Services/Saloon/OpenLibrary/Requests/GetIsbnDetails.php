<?php

declare(strict_types=1);

namespace App\Services\Saloon\OpenLibrary\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetIsbnDetails extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $isbn,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/isbn/' . $this->isbn . '.json';
    }
}
