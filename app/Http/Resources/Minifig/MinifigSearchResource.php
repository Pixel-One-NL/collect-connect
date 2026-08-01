<?php

declare(strict_types=1);

namespace App\Http\Resources\Minifig;

use App\Models\Minifig;
use App\Models\Product;
use App\Support\BricqerImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Minifig
 */
class MinifigSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing(['media', 'products']);

        /** @var Product|null $product */
        $product = $this->products
            ->sortByDesc('stock')
            ->first();

        $price = $product?->price;

        return [
            'id' => $product?->id ?? $this->id,
            'title' => $this->name,
            'lego_number' => $this->bricklink_id,
            'rebrickable_id' => $this->rebrickable_id,
            'url' => $product !== null
                ? route('product.show', $product->id)
                : route('catalog.minifigs'),
            'image' => $this->imageUrl(),
            'priceMin' => $price,
            'priceMax' => $price,
            'sibling_colors' => [],
            'type' => 'minifig',
        ];
    }

    protected function imageUrl(): ?string
    {
        $mediaUrl = $this
            ->getFirstMedia(Minifig::BRICQER_IMAGE_COLLECTION)
            ?->getAvailableUrl([Minifig::THUMB_CONVERSION]);

        if ($mediaUrl) {
            return $mediaUrl;
        }

        return $this->bricklink_id
            ? BricqerImageUrl::minifig((string) $this->bricklink_id)
            : null;
    }
}
