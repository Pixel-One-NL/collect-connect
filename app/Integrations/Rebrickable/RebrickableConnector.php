<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable;

use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class RebrickableConnector extends Connector
{
    use AlwaysThrowOnErrors;

    public function __construct(
        protected readonly string $key,
    ) {}

    public function resolveBaseUrl(): string
    {
        return 'https://rebrickable.com/api/v3';
    }

    protected function defaultAuth(): HeaderAuthenticator
    {
        return new HeaderAuthenticator("key {$this->key}");
    }

    public function resolveResponseClass(): string
    {
        return RebrickableResponse::class;
    }
}
