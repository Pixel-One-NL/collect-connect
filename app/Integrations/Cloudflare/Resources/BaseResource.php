<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Resources;

use App\Integrations\Cloudflare\CloudflareConnector;

abstract class BaseResource
{
    public function __construct(
        protected CloudflareConnector $connector,
    ) {}

    public function getConnector(): CloudflareConnector
    {
        return $this->connector;
    }
}
