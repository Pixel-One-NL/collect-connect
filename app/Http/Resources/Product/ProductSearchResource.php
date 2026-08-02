<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Http\Resources\Color\ColorResource;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

class ProductSearchResource extends ProductResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing([
            'color',
            'productable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                Part::class => ['products.color', 'partColors.media'],
                Minifig::class => ['media'],
            ]),
        ]);

        $siblings = $this->productable instanceof Part
            ? ($this->productable->products ?? collect())
            : collect();
        $priceMin = (int) ($siblings->min('price') ?? $this->price);
        $priceMax = (int) ($siblings->max('price') ?? $this->price);

        return [
            'id' => $this->id,
            'title' => $this->productable->name,
            'lego_number' => $this->productable->bricklink_id,
            'url' => route('product.show', $this->resource, absolute: false),
            'image' => $this->getImage(),
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'sibling_colors' => $this->productable instanceof Part
                ? $siblings
                    ->sortBy(fn (Product $product): string => $product->color?->name ?? '')
                    ->values()
                    ->map(fn (Product $product): array => [
                        'id' => $product->id,
                        'stock' => $this->getSafeStock($product->stock),
                        'price' => $product->price,
                        'image' => $this->getPartImage($product->productable, $product->color_id),
                        'color' => ColorResource::make($product->color)->resolve(),
                    ])
                    ->all()
                : [],
        ];
    }
}
