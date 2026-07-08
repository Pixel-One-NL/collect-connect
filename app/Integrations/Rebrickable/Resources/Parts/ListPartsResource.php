<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Resources\Parts;

use App\Integrations\Rebrickable\DataTransferObjects\Part;
use App\Integrations\Rebrickable\Requests\Parts\ListPartsRequest;
use App\Integrations\Rebrickable\Resources\PaginatedResource;

/**
 * @extends PaginatedResource<Part>
 */
class ListPartsResource extends PaginatedResource
{
    /**
     * @var ?list<string>
     */
    public ?array $partNumbers = null;

    public ?string $partCategoryId = null;

    public ?string $colorId = null;

    public ?string $bricklinkId = null;

    public ?string $brickOwlId = null;

    public ?string $legoId = null;

    public ?string $ldrawId = null;

    public ?string $search = null;

    public function getRequest(): ListPartsRequest
    {
        return new ListPartsRequest(
            partNumbers: $this->partNumbers,
            partCategoryId: $this->partCategoryId,
            colorId: $this->colorId,
            bricklinkId: $this->bricklinkId,
            brickOwlId: $this->brickOwlId,
            legoId: $this->legoId,
            ldrawId: $this->ldrawId,
            search: $this->search,
        );
    }

    /**
     * @param  ?list<string>  $partNumbers
     */
    public function partNumbers(?array $partNumbers): self
    {
        $this->partNumbers = $partNumbers;

        return $this;
    }

    public function partCategoryId(?string $partCategoryId): self
    {
        $this->partCategoryId = $partCategoryId;

        return $this;
    }

    public function colorId(?string $colorId): self
    {
        $this->colorId = $colorId;

        return $this;
    }

    public function bricklinkId(?string $bricklinkId): self
    {
        $this->bricklinkId = $bricklinkId;

        return $this;
    }

    public function brickOwlId(?string $brickOwlId): self
    {
        $this->brickOwlId = $brickOwlId;

        return $this;
    }

    public function legoId(?string $legoId): self
    {
        $this->legoId = $legoId;

        return $this;
    }

    public function ldrawId(?string $ldrawId): self
    {
        $this->ldrawId = $ldrawId;

        return $this;
    }

    public function search(?string $search): self
    {
        $this->search = $search;

        return $this;
    }
}
