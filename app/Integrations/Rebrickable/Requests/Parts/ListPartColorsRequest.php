<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Requests\Parts;

use App\Integrations\Rebrickable\DataTransferObjects\PartColor;
use App\Integrations\Rebrickable\PaginatedCollection;
use App\Integrations\Rebrickable\Requests\PaginatedRequest;
use JsonException;
use Saloon\Http\Response;

class ListPartColorsRequest extends PaginatedRequest
{
    public function __construct(
        public string $partNumber,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function resolveEndpoint(): string
    {
        return "/lego/parts/{$this->partNumber}/colors";
    }

    /**
     * @return PaginatedCollection<int, PartColor>
     *
     * @throws JsonException
     */
    public function createDtoFromResponse(Response $response): PaginatedCollection
    {
        /** @var PaginatedCollection<int, PartColor> */
        return PartColor::collect($response->json('results'), PaginatedCollection::class);
    }
}
