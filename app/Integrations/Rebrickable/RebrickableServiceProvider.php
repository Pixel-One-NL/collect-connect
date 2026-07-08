<?php

declare(strict_types=1);

namespace App\Integrations\Rebrickable;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class RebrickableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RebrickableConnector::class,
            fn (): RebrickableConnector => new RebrickableConnector(config('rebrickable.key'))
        );

        $this->app->bind(
            RebrickableApi::class,
            fn (Application $app): RebrickableApi => new RebrickableApi($app->make(RebrickableConnector::class))
        );
    }
}
