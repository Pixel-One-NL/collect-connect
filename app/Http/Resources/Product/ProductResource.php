<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Http\Resources\Color\ColorResource;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing('productable.partColors', 'color');

        return [
            'id' => $this->id,
            'stock' => $this->getSafeStock($this->stock),
            'price' => $this->price,

            'title' => $this->getTitle(),
            'image' => $this->getImage(),
            'lego_number' => $this->productable->bricklink_id,

            'color' => ColorResource::make($this->color),

            'url' => route('product.show', $this->id),

            ...$this->productable instanceof Part
                ? ['sibling_colors' => $this->productable->products->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'stock' => $this->getSafeStock($product->stock),
                    'price' => $product->price,

                    'image' => $this->getPartImage($product->productable, $product->color_id),

                    'color' => ColorResource::make($product->color),
                ])]
                : [],
        ];
    }

    protected function getTitle(): string
    {
        if ($this->productable instanceof Part) {
            return $this->productable->name;
        }

        if ($this->productable instanceof Minifig) {
            return $this->productable->name;
        }

        throw new Exception('Productable type not found');
    }

    protected function getPartImage(Part $part, int $colorId): ?string
    {
        $partColor = $part->partColors->firstWhere('color_id', $colorId);

        return $partColor
            ?->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION)
            ?->getAvailableUrl([PartColor::LARGE_CONVERSION]);
    }

    protected function getImage(): ?string
    {
        if ($this->productable instanceof Part) {
            return $this->getPartImage($this->productable, $this->color_id);
        }

        throw new Exception('Not implemented');
    }

    protected function getSafeStock(int $stock): int
    {
        if ($stock <= 100) {
            return $stock;
        }

        return 101;
    }
}
