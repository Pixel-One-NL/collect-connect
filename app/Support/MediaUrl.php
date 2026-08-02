<?php

declare(strict_types=1);

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Build browser-safe media URLs that do not depend on APP_URL being correct
 * (important when the app is served behind Docker, Traefik, or a public host
 * that differs from config('app.url')).
 */
final class MediaUrl
{
    /**
     * @param  list<string>  $conversions  Preferred conversion names, in order.
     */
    public static function fromMedia(?Media $media, array $conversions = []): ?string
    {
        if ($media === null) {
            return null;
        }

        $url = $conversions === []
            ? $media->getUrl()
            : $media->getAvailableUrl($conversions);

        return self::toRelative(is_string($url) ? $url : null);
    }

    public static function toRelative(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : $url;
    }
}
