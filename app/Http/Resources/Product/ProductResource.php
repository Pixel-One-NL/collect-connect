<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Http\Resources\Color\ColorResource;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use App\Support\MediaUrl;
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
        $this->loadMissing(['color', 'productable']);

        if ($this->productable instanceof Part) {
            $this->productable->loadMissing(['partColors.media', 'partCategory', 'products.color']);
        }

        if ($this->productable instanceof Minifig) {
            $this->productable->loadMissing('media');
        }

        $productable = $this->productable;

        return [
            'id' => $this->id,
            'stock' => $this->getSafeStock($this->stock),
            'price' => $this->price,
            'title' => $this->getTitle(),
            'image' => $this->getImage(),
            'lego_number' => $productable->bricklink_id,
            'rebrickable_id' => $productable->rebrickable_id ?? null,
            'type' => $productable instanceof Minifig ? 'minifig' : 'part',
            'category' => $productable instanceof Part
                ? $productable->partCategory?->name
                : null,
            'color' => $this->shouldShowColor()
                ? ColorResource::make($this->color)
                : null,
            'url' => route('product.show', $this->resource, absolute: false),
            'attributes' => $this->attributesList(),
            ...$productable instanceof Part
                ? ['sibling_colors' => $productable->products->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'stock' => $this->getSafeStock($product->stock),
                    'price' => $product->price,
                    'image' => $this->getPartImage($product->productable, $product->color_id),
                    'color' => ColorResource::make($product->color),
                    'url' => route('product.show', $product, absolute: false),
                ])]
                : ['sibling_colors' => []],
        ];
    }

    protected function getTitle(): string
    {
        if ($this->productable instanceof Part || $this->productable instanceof Minifig) {
            return (string) $this->productable->name;
        }

        throw new Exception('Productable type not found');
    }

    /**
     * Shop images come only from the Spatie media library. Bricqer CDN URLs are
     * for import jobs only — never hotlinked to the storefront.
     */
    protected function getPartImage(Part $part, int $colorId): ?string
    {
        $partColor = $part->partColors->firstWhere('color_id', $colorId);

        return MediaUrl::fromMedia(
            $partColor?->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION),
            [PartColor::LARGE_CONVERSION, PartColor::MEDIUM_CONVERSION, PartColor::THUMB_CONVERSION],
        );
    }

    protected function getImage(): ?string
    {
        if ($this->productable instanceof Part) {
            return $this->getPartImage($this->productable, (int) $this->color_id);
        }

        if ($this->productable instanceof Minifig) {
            return $this->getMinifigImage($this->productable);
        }

        return null;
    }

    protected function getMinifigImage(Minifig $minifig): ?string
    {
        return MediaUrl::fromMedia(
            $minifig->getFirstMedia(Minifig::BRICQER_IMAGE_COLLECTION),
            [Minifig::LARGE_CONVERSION, Minifig::MEDIUM_CONVERSION, Minifig::THUMB_CONVERSION],
        );
    }

    protected function shouldShowColor(): bool
    {
        if ($this->productable instanceof Minifig) {
            return false;
        }

        return $this->color !== null
            && $this->color->bricklink_color_id !== '0'
            && $this->color->name !== '(Not Applicable)';
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    protected function attributesList(): array
    {
        $attrs = [
            ['label' => 'Type', 'value' => $this->productable instanceof Minifig ? 'Minifiguur' : 'Onderdeel'],
            ['label' => 'LEGO-nummer', 'value' => (string) ($this->productable->bricklink_id ?? '—')],
        ];

        if ($this->productable instanceof Part && $this->productable->rebrickable_id) {
            $attrs[] = ['label' => 'Rebrickable', 'value' => (string) $this->productable->rebrickable_id];
        }

        if ($this->productable instanceof Part && $this->productable->partCategory) {
            $attrs[] = ['label' => 'Categorie', 'value' => (string) $this->productable->partCategory->name];
        }

        if ($this->shouldShowColor() && $this->color) {
            $attrs[] = ['label' => 'Kleur', 'value' => (string) $this->color->name];
        }

        $weight = data_get($this->productable, 'weight_grams');
        if ($weight !== null) {
            $attrs[] = ['label' => 'Gewicht', 'value' => rtrim(rtrim(number_format((float) $weight, 4, '.', ''), '0'), '.').' g'];
        }

        $attrs[] = ['label' => 'Voorraad', 'value' => (string) $this->getSafeStock($this->stock)];

        return $attrs;
    }

    protected function getSafeStock(int $stock): int
    {
        if ($stock <= 100) {
            return $stock;
        }

        return 101;
    }
}
