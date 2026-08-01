<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Demo shipping fallbacks
    |--------------------------------------------------------------------------
    |
    | When no active ShippingMethod rows exist, demo rates may be used in local
    | and testing environments. Production should fail closed.
    |
    */
    'allow_demo_fallback' => env(
        'SHIPPING_ALLOW_DEMO_FALLBACK',
        env('APP_ENV', 'production') === 'local' || env('APP_ENV') === 'testing',
    ),
];
