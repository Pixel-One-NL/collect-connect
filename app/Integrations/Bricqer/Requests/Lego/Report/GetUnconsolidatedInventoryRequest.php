<?php

namespace App\Integrations\Bricqer\Requests\Lego\Report;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetUnconsolidatedInventoryRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/definitions/lego/report/unconsolidated-inventory';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'text/csv',
        ];
    }

    public function createDtoFromResponse(Response $response): mixed
    {
    }
}
