<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class CloudflareConnector extends Connector
{
    use AlwaysThrowOnErrors;

    public function __construct(protected string $accountId, protected string $apiToken) {}

    /**
     * @return array<string, int>
     */
    protected function defaultConfig(): array
    {
        return [
            'connect_timeout' => 10,
            'timeout' => 45,
        ];
    }

    public function resolveBaseUrl(): string
    {
        return "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}";
    }

    protected function defaultAuth(): Authenticator
    {
        return new TokenAuthenticator($this->apiToken);
    }
}
