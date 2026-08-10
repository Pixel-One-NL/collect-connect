<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrendingProducts\Support;

use App\Models\Product;

/**
 * Human readable label for a product in the admin panel. Products have no name
 * of their own; the name lives on the polymorphic part or minifig.
 */
final class ProductLabel
{
    public static function for(?Product $product): ?string
    {
        if ($product === null) {
            return null;
        }

        $productable = $product->productable;
        $bricklinkId = $productable?->bricklink_id;

        $segments = array_filter([
            (string) ($productable->name ?? 'Onbekend product'),
            $bricklinkId !== null ? "({$bricklinkId})" : null,
            $product->color?->name,
        ]);

        return implode(' ', $segments);
    }
}
