<?php

namespace App\Integrations\Bricqer\Resources\Lego\Report;

use App\Integrations\Bricqer\Requests\Lego\Report\GetUnconsolidatedInventoryRequest;
use App\Integrations\Bricqer\Resources\BaseResource;
use Illuminate\Support\Collection;

class UnconsolidatedInventoryResource extends BaseResource
{
    public function get(): Collection
    {
        return $this->connector->send(new GetUnconsolidatedInventoryRequest)->dtoOrFail();
    }
}
