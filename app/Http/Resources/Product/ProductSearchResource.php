<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Http\Resources\Color\ColorResource;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchResource extends ProductResource
{
    public function toArray(Request $request): array
    {
        $siblings = $this->productable->products;
        $priceMin = $siblings->min('price');
        $priceMax = $siblings->max('price');

        return [
            'id' => $this->id,
            'title' => $this->productable->name,
            'legoNumber' => $this->productable->bricklink_id,
            'url' => "/products/{$this->id}",
            'image' => $this->getImage(),
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'lego_number' => $this->productable->bricklink_id,

            ...$this->productable instanceof Part
                ? ['sibling_colors' => $this->productable->products->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'stock' => $this->getSafeStock($product->stock),
                    'price' => $product->price,

                    'image' => $this->getPartImage($product->productable, $product->color_id),

                    'color' => ColorResource::make($product->color),
                ])] : [],
        ];
    }
}
