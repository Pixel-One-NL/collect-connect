<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer;

use App\Integrations\Bricqer\Resources\Definition\DefinitionResource;
use App\Integrations\Bricqer\Resources\InventoryResource;
use App\Integrations\Bricqer\Resources\LegoResource;
use App\Integrations\Bricqer\Resources\LegoVisualResource;

class BricqerApi
{
    public function __construct(protected BricqerConnector $connector) {}

    public function lego(): LegoResource
    {
        return new LegoResource($this->connector);
    }

    public function legoVisual(): LegoVisualResource
    {
        return new LegoVisualResource($this->connector);
    }

    public function inventory(): InventoryResource
    {
        return new InventoryResource($this->connector);
    }

    public function definition(): DefinitionResource
    {
        return new DefinitionResource($this->connector);
    }
}
