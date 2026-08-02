<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Builds Bricqer CDN source URLs for **backend image import jobs only**.
 *
 * Never use these URLs in storefront responses (product pages, search, cart,
 * set BOM). Shop images must come from the Spatie media library after import.
 *
 * Pattern is observed from Bricqer payloads; not fully documented in OpenAPI.
 */
final class BricqerImageUrl
{
    public static function part(string $bricklinkId, string|int $bricklinkColorId): string
    {
        return sprintf(
            'https://cdn.bricqer.com/static/bl-cache/PN/%s/%s.png',
            rawurlencode((string) $bricklinkColorId),
            rawurlencode($bricklinkId),
        );
    }

    public static function minifig(string $bricklinkId): string
    {
        return sprintf(
            'https://cdn.bricqer.com/static/bl-cache/MN/0/%s.png',
            rawurlencode($bricklinkId),
        );
    }
}
