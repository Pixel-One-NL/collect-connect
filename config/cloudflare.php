<?php

declare(strict_types=1);

return [
    'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
    'api_token' => env('CLOUDFLARE_IMAGES_API_TOKEN'),
    'account_hash' => env('CLOUDFLARE_IMAGES_ACCOUNT_HASH'),
    'delivery_url' => env('CLOUDFLARE_IMAGES_DELIVERY_URL', 'https://imagedelivery.net'),

    /*
     * Prepended to every custom image id so environments can share one account.
     * Developers on the same account should each set their own value.
     */
    'id_prefix' => env('CLOUDFLARE_IMAGES_ID_PREFIX', env('APP_ENV', 'production')),

    /*
     * Cloudflare API error codes with a dedicated meaning. Both are undocumented:
     * record them from real API responses. An error whose code is not listed is
     * retried and then fails the job, so a bad token can never look like "no image".
     */
    'error_codes' => [
        // 5409: "Resource already exists" (seen 2026-11-17, HTTP 409)
        'image_already_exists' => [5409],
        // 5454: "Error during the fetch, code: 404, ..." (seen 2026-11-17)
        'source_unreachable' => [5454],
    ],

    /*
     * Same sizes as the former media library conversions. Spatie's Fit::Contain
     * and Cloudflare's "contain" behave alike: keep the aspect ratio, may upscale.
     *
     * @var array<string, array{width: int, height: int, fit: string}>
     */
    'variants' => [
        'thumb' => ['width' => 200, 'height' => 200, 'fit' => 'contain'],
        'medium' => ['width' => 400, 'height' => 400, 'fit' => 'contain'],
        'large' => ['width' => 800, 'height' => 800, 'fit' => 'contain'],
    ],
];
