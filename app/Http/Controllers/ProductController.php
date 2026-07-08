<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Product\ProductResource;
use App\Models\Part;
use App\Models\Product;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(Product $product): Response
    {
        $product->load('productable.partColors.media', 'color');

        $suggestions = collect();

        // If the product is a part, show suggestions
        if ($product->productable instanceof Part) {
            $bricklinkId = str($product->productable->bricklink_id)
                // Strip everything after the first letter
                ->replaceMatches('/[a-zA-Z].*/', '')
                ->toString();

            $suggestions = Product::query()
                ->whereMorphedTo('productable', Part::class)
                ->whereHas('productable', function ($query) use ($bricklinkId) {
                    $query->where('bricklink_id', 'like', "{$bricklinkId}%");
                })
                ->where('id', '!=', $product->id)
                ->where('productable_id', '!=', $product->productable_id)
                ->with(['color', 'productable'])
                ->limit(10)
                ->get()
                ->unique(fn (Product $product) => "{$product->productable_type}-{$product->productable_id}");
        }

        return inertia('shop/product', [
            'product' => ProductResource::make($product),
            'suggestions' => ProductResource::collection($suggestions),
        ]);
    }
}
