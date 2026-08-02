<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Minifig\MinifigSearchResource;
use App\Http\Resources\Product\ProductSearchResource;
use App\Http\Resources\Set\SetSearchResource;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Product;
use App\Models\Set;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search parts (as products), minifigs, and sets via Scout and return
     * combined results.
     *
     * Part products are collapsed by part (one card per part, colors merged).
     * Minifigs are returned from the Minifig index. Sets are returned directly.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json([
                'data' => [
                    'products' => [],
                    'minifigs' => [],
                    'sets' => [],
                ],
            ]);
        }

        $partMorph = (new Part)->getMorphClass();
        $builder = Product::search($query);

        // Typesense/Meilisearch/Algolia can filter on the indexed `type` field.
        // Collection/database engines apply where() as SQL against products and
        // have no `type` column, so they fall through to the PHP filter below.
        if (! in_array(config('scout.driver'), ['collection', 'database', 'null'], true)) {
            $builder->where('type', 'part');
        }

        $products = $builder
            ->take(100)
            ->get()
            ->filter(fn (Product $product): bool => $product->productable_type === $partMorph && $product->stock > 0)
            ->unique(fn (Product $product): string => "{$product->productable_type}_{$product->productable_id}")
            ->values()
            ->load([
                'color',
                'productable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Part::class => ['partColors.media', 'products.color'],
                ]),
            ]);

        $minifigs = Minifig::search($query)
            ->take(40)
            ->get()
            ->load(['media', 'products'])
            // Shop search only returns minifigs that link to a sellable product page.
            ->filter(fn (Minifig $minifig): bool => $minifig->products->contains(
                fn (Product $product): bool => $product->stock > 0,
            ))
            ->values();

        $sets = Set::search($query)
            ->take(20)
            ->get()
            ->load('media');

        return response()->json([
            'data' => [
                'products' => $products
                    ->map(fn (Product $product): array => (new ProductSearchResource($product))->resolve())
                    ->values()
                    ->all(),
                'minifigs' => $minifigs
                    ->map(fn (Minifig $minifig): array => (new MinifigSearchResource($minifig))->resolve())
                    ->values()
                    ->all(),
                'sets' => $sets
                    ->map(fn (Set $set): array => (new SetSearchResource($set))->resolve())
                    ->values()
                    ->all(),
            ],
        ]);
    }
}
