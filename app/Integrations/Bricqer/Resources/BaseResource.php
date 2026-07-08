<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Resources;

use App\Integrations\Bricqer\BricqerConnector;

abstract class BaseResource
{
    public function __construct(
        protected BricqerConnector $connector,
    ) {}

    public function getConnector(): BricqerConnector
    {
        return $this->connector;
    }
}
