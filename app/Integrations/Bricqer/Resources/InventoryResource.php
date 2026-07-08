<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Resources;

use App\Integrations\Bricqer\DataTransferObjects\Inventory\Color;
use App\Integrations\Bricqer\Requests\Inventory\GetColorsRequest;
use Illuminate\Support\Collection;

class InventoryResource extends BaseResource
{
    /**
     * @return Collection<int, Color>
     */
    public function colors(): Collection
    {
        return $this->connector->send(new GetColorsRequest)->dtoOrFail();
    }
}
