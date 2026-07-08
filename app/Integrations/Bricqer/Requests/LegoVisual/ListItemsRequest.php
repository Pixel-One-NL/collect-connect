<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Requests\LegoVisual;

use App\Integrations\Bricqer\DataTransferObjects\LegoVisual\LegoVisualPage;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListItemsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ?string $partType = null,
        protected int $page = 1,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/lego-visual/item';
    }

    /**
     * Bricqer paginates with a bare `page=` query param (no page size). The
     * part type filter is optional, so empty values are stripped.
     *
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'part_type' => $this->partType,
            'page' => $this->page,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    public function createDtoFromResponse(Response $response): LegoVisualPage
    {
        return LegoVisualPage::fromResponse($response->json());
    }
}
