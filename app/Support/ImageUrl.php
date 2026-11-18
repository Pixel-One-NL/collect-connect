<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Minifig;
use App\Models\Part;
use App\Models\Pivots\PartColor;

/**
 * Builds Cloudflare Images delivery URLs. Shop images are only ever served from
 * Cloudflare: source CDN URLs are for import jobs and never reach the storefront.
 */
final class ImageUrl
{
    public static function fromId(?string $imageId, ImageVariant $variant = ImageVariant::Large): ?string
    {
        if ($imageId === null || $imageId === '') {
            return null;
        }

        return sprintf(
            '%s/%s/%s/%s',
            rtrim((string) config('cloudflare.delivery_url'), '/'),
            config('cloudflare.account_hash'),
            $imageId,
            $variant->value,
        );
    }

    public static function forPart(Part $part, int $colorId, ImageVariant $variant = ImageVariant::Large): ?string
    {
        return $part->partColors->firstWhere('color_id', $colorId)?->imageUrl($variant);
    }

    /**
     * Image for listings that represent a whole part (search cards): use the
     * exact color image when it exists, otherwise any other color of the same
     * part, so a single color without an image never blanks out the card.
     */
    public static function forPartAnyColor(Part $part, int $colorId, ImageVariant $variant = ImageVariant::Large): ?string
    {
        $partColors = $part->partColors
            ->sortByDesc(fn (PartColor $partColor): bool => $partColor->color_id === $colorId);

        foreach ($partColors as $partColor) {
            $url = $partColor->imageUrl($variant);

            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    public static function forMinifig(Minifig $minifig, ImageVariant $variant = ImageVariant::Large): ?string
    {
        return $minifig->imageUrl($variant);
    }
}
