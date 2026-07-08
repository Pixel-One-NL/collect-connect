<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Requests\Inventory;

use App\Integrations\Bricqer\DataTransferObjects\Inventory\Color;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetColorsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/inventory/color';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    /**
     * @return Collection<int, Color>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        /** @var Collection<int, Color> */
        return Color::collect($response->json(), Collection::class);
    }
}
