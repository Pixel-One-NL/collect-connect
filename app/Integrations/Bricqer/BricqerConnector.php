<?php

namespace App\Integrations\Bricqer;

use InvalidArgumentException;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class BricqerConnector extends Connector
{
    use AlwaysThrowOnErrors;

    public function __construct(
        protected string $domain,
        protected string $apiKey,
    ) {}

    public function resolveBaseUrl(): string
    {
        if(!preg_match('/^[a-z0-9]+\.bricqer\.com$/', $this->domain)) {
            throw new InvalidArgumentException('The domain must be in the format xxxx.bricqer.com');
        }

        return "https://{$this->domain}/api/v1";
    }
}
