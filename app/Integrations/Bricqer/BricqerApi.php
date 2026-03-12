<?php

namespace App\Integrations\Bricqer;

use App\Integrations\Bricqer\Resources\LegoResource;

class BricqerApi
{
    public function __construct(protected BricqerConnector $connector) {}

    public function lego(): LegoResource
    {
        return new LegoResource($this->connector);
    }
}
