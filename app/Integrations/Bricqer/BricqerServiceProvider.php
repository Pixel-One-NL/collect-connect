<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class BricqerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BricqerConnector::class,
            fn (): BricqerConnector => new BricqerConnector(config('bricqer.domain'), config('bricqer.api_key')),
        );

        $this->app->bind(
            BricqerApi::class,
            fn (Application $app): BricqerApi => new BricqerApi($app->make(BricqerConnector::class)),
        );
    }
}
