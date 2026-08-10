<?php

declare(strict_types=1);

use App\Domain\Payment\Drivers\ManualPaymentGateway;
use App\Domain\Payment\Drivers\TestingPaymentGateway;

return [
    /*
    |--------------------------------------------------------------------------
    | Default payment driver
    |--------------------------------------------------------------------------
    |
    | Local and staging default to the "testing" driver so no real payment is
    | ever attempted there. Everything else fails safe to "manual". Setting
    | PAYMENT_DRIVER explicitly always wins, so a staging box can be pointed
    | at a real sandbox PSP without a code change.
    |
    | Note: PHPUnit runs with APP_ENV=testing (see phpunit.xml), which is NOT
    | in the list below - the test suite uses "manual" unless a test overrides
    | it. The driver named "testing" and the "testing" environment are separate
    | things that happen to share a word.
    |
    */
    'default' => env('PAYMENT_DRIVER')
        ?: (in_array(env('APP_ENV', 'production'), ['local', 'staging'], true) ? 'testing' : 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | Map a driver name to a class implementing App\Domain\Payment\Contracts\
    | PaymentGateway. Adding a provider means adding a class plus a line here.
    |
    */
    'drivers' => [
        'manual' => ManualPaymentGateway::class,
        'testing' => TestingPaymentGateway::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment simulator environments
    |--------------------------------------------------------------------------
    |
    | Environments where the fake payment page (routes + controller) is allowed
    | to exist. Production must never appear here; TestingPaymentGateway also
    | refuses to boot there regardless of this list.
    |
    */
    'simulator_environments' => ['local', 'staging', 'testing'],
];
