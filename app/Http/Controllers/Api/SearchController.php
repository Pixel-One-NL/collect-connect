<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductSearchResource;
use App\Http\Resources\Set\SetSearchResource;
use App\Models\Product;
use App\Models\Set;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search products and sets via Scout (Typesense) and return combined results.
     *
     * Products are collapsed by part (one card per part, all colors merged).
     * Sets are returned directly, ordered by relevance.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json(['data' => ['products' => [], 'sets' => []]]);
        }

        $products = Product::search($query)
            ->take(100)
            ->get();

        $uniqueProducts = $products
            ->unique(fn (Product $product): string => "{$product->productable_type}_{$product->productable_id}")
            ->load('productable.partColors.media', 'productable.products');

        $sets = Set::search($query)
            ->take(20)
            ->get()
            ->load('media');

        return response()->json([
            'data' => [
                'products' => $uniqueProducts->map(fn (Product $product): array => (new ProductSearchResource($product))->resolve())->values(),
                'sets' => $sets->map(fn (Set $set): array => (new SetSearchResource($set))->resolve())->values(),
            ],
        ]);
    }
}
