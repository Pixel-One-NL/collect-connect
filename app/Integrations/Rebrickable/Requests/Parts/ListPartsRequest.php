<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Requests\Parts;

use App\Integrations\Rebrickable\DataTransferObjects\Part;
use App\Integrations\Rebrickable\PaginatedCollection;
use App\Integrations\Rebrickable\Requests\PaginatedRequest;
use JsonException;
use Saloon\Http\Response;

class ListPartsRequest extends PaginatedRequest
{
    /**
     * @param  ?list<string>  $partNumbers
     */
    public function __construct(
        public ?array $partNumbers = null,
        public ?string $partCategoryId = null,
        public ?string $colorId = null,
        public ?string $bricklinkId = null,
        public ?string $brickOwlId = null,
        public ?string $legoId = null,
        public ?string $ldrawId = null,
        public ?string $search = null,
    ) {}

    protected function defaultQuery(): array
    {
        return array_filter([
            'part_nums' => $this->partNumbers ? implode(',', $this->partNumbers) : null,
            'part_cat_id' => $this->partCategoryId,
            'color_id' => $this->colorId,
            'bricklink_id' => $this->bricklinkId,
            'brickowl_id' => $this->brickOwlId,
            'lego_id' => $this->legoId,
            'ldraw_id' => $this->ldrawId,
            'search' => $this->search,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveEndpoint(): string
    {
        return '/lego/parts';
    }

    /**
     * @return PaginatedCollection<int, Part>
     *
     * @throws JsonException
     */
    public function createDtoFromResponse(Response $response): PaginatedCollection
    {
        /** @var PaginatedCollection<int, Part> */
        return Part::collect($response->json('results'), PaginatedCollection::class);
    }
}
