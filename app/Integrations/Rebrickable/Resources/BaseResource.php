<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable\Resources;

use App\Integrations\Rebrickable\RebrickableConnector;

abstract class BaseResource
{
    public function __construct(
        protected RebrickableConnector $connector,
    ) {}

    public function getConnector(): RebrickableConnector
    {
        return $this->connector;
    }
}
