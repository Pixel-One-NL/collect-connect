<?php

declare(strict_types=1);

use App\Domain\Rebrickable\Providers\RebrickableServiceProvider;
use App\Http\Middleware\HandleInertiaRequests;
use App\Integrations\Bricqer\BricqerServiceProvider;
use App\Integrations\Rebrickable\RebrickableServiceProvider as RebrickableIntegrationServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        RebrickableServiceProvider::class,
        RebrickableIntegrationServiceProvider::class,
        BricqerServiceProvider::class,
    ])
    ->withCommands([
        app_path('Domain/Bricqer/Commands'),
        app_path('Domain/Rebrickable/Commands'),
        app_path('Domain/Minifig/Commands'),
        app_path('Domain/Part/Commands'),
        app_path('Domain/Product/Commands'),
        app_path('Domain/Color/Commands'),
    ])
    ->create();
