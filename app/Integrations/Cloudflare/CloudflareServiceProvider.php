<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class CloudflareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CloudflareConnector::class,
            fn (): CloudflareConnector => new CloudflareConnector((string) config('cloudflare.account_id'), (string) config('cloudflare.api_token')),
        );

        $this->app->bind(
            CloudflareApi::class,
            fn (Application $app): CloudflareApi => new CloudflareApi($app->make(CloudflareConnector::class)),
        );
    }
}
