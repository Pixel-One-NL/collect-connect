<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Resources;

use App\Integrations\Rebrickable\Resources\Parts\ListPartColorsResource;
use App\Integrations\Rebrickable\Resources\Parts\ListPartsResource;

class PartsResource extends BaseResource
{
    public function list(): ListPartsResource
    {
        return new ListPartsResource($this->connector);
    }

    public function colors(string $partNumber): ListPartColorsResource
    {
        return (new ListPartColorsResource($this->connector))->partNumber($partNumber);
    }
}
