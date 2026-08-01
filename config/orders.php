<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Unpaid stock reservation TTL
    |--------------------------------------------------------------------------
    |
    | Pending-payment orders that still hold stock older than this many minutes
    | are cancelled and restocked by the orders:release-unpaid-stock command.
    |
    */
    'reservation_ttl_minutes' => (int) env('ORDERS_RESERVATION_TTL_MINUTES', 60),
];
