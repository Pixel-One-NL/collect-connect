<?php

namespace App\Integrations\Bricqer\Resources\Lego;

use App\Integrations\Bricqer\Resources\BaseResource;
use App\Integrations\Bricqer\Resources\Lego\Report\UnconsolidatedInventoryResource;

class ReportResource extends BaseResource
{
    public function unconsolidatedInventory(): UnconsolidatedInventoryResource
    {
        return new UnconsolidatedInventoryResource($this->connector);
    }
}
