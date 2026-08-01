<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Bricqer CDN image URLs observed from live order payloads.
 * Pattern is not fully documented in the OpenAPI export.
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
