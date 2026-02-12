<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable;

use App\Integrations\Rebrickable\Resources\PartsResource;
use Saloon\Laravel\Facades\Saloon;

class RebrickableApi extends Saloon
{
    public function __construct(protected RebrickableConnector $connector) {}

    public function parts(): PartsResource
    {
        return new PartsResource($this->connector);
    }

    public function debug(): self
    {
        return new self($this->connector->debug());
    }
}
