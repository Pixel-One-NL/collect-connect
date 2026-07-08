<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Resources\Parts;

use App\Integrations\Rebrickable\DataTransferObjects\PartColor;
use App\Integrations\Rebrickable\Requests\Parts\ListPartColorsRequest;
use App\Integrations\Rebrickable\Resources\PaginatedResource;

/**
 * @extends PaginatedResource<PartColor>
 */
class ListPartColorsResource extends PaginatedResource
{
    public ?string $partNumber = null;

    public function getRequest(): ListPartColorsRequest
    {
        return new ListPartColorsRequest(
            partNumber: (string) $this->partNumber,
        );
    }

    public function partNumber(string $partNumber): self
    {
        $this->partNumber = $partNumber;

        return $this;
    }
}
