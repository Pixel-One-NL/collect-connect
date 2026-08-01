<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Product\Queries\ProductListingQuery;
use App\Http\Resources\Product\ProductResource;
use App\Models\Color;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Response;

class CatalogController extends Controller
{
    public function parts(Request $request): Response
    {
        return $this->listing($request, type: 'part');
    }

    public function minifigs(Request $request): Response
    {
        return $this->listing($request, type: 'minifig');
    }

    public function search(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->integer('category_id') ?: null;
        $colorId = $request->integer('color_id') ?: null;

        $query = Product::query()
            ->with(ProductListingQuery::defaultWith())
            ->where('stock', '>', 0)
            ->when($colorId, fn (Builder $builder) => $builder->where('color_id', $colorId))
            ->when($categoryId, function (Builder $builder) use ($categoryId): void {
                $builder->whereHasMorph(
                    'productable',
                    [Part::class],
                    fn (Builder $part) => $part->where('part_category_id', $categoryId),
                );
            })
            ->when($q !== '', function (Builder $builder) use ($q): void {
                $builder->where(function (Builder $inner) use ($q): void {
                    $inner->whereHasMorph(
                        'productable',
                        [Part::class, Minifig::class],
                        function (Builder $productable) use ($q): void {
                            $productable->where(function (Builder $match) use ($q): void {
                                $match
                                    ->where('name', 'like', "%{$q}%")
                                    ->orWhere('bricklink_id', 'like', "%{$q}%");
                            });
                        },
                    );
                });
            })
            ->orderByDesc('stock');

        $products = $query->paginate(48)->withQueryString();

        return inertia('shop/catalog', [
            'title' => $q !== '' ? "Zoekresultaten voor “{$q}”" : 'Zoeken',
            'type' => 'search',
            'query' => $q,
            'products' => ProductResource::collection($products),
            'filters' => [
                'categories' => PartCategory::query()->orderBy('name')->get(['id', 'name']),
                'colors' => Color::query()
                    ->where('bricklink_color_id', '!=', '0')
                    ->orderBy('name')
                    ->limit(80)
                    ->get(['id', 'name', 'hex']),
            ],
            'active' => [
                'category_id' => $categoryId,
                'color_id' => $colorId,
                'q' => $q,
            ],
        ]);
    }

    protected function listing(Request $request, string $type): Response
    {
        $morph = $type === 'minifig' ? Minifig::class : Part::class;
        $categoryId = $request->integer('category_id') ?: null;
        $colorId = $request->integer('color_id') ?: null;

        $query = Product::query()
            ->with(ProductListingQuery::forType($type))
            ->where('productable_type', (new $morph)->getMorphClass())
            ->where('stock', '>', 0)
            ->when($colorId, fn ($q) => $q->where('color_id', $colorId))
            ->when($categoryId && $type === 'part', function ($q) use ($categoryId): void {
                $q->whereHasMorph('productable', [Part::class], fn ($p) => $p->where('part_category_id', $categoryId));
            })
            ->orderByDesc('stock');

        $products = $query->paginate(48)->withQueryString();

        return inertia('shop/catalog', [
            'title' => $type === 'minifig' ? 'Minifiguren' : 'Onderdelen',
            'type' => $type,
            'query' => null,
            'products' => ProductResource::collection($products),
            'filters' => [
                'categories' => $type === 'part'
                    ? PartCategory::query()->orderBy('name')->get(['id', 'name'])
                    : [],
                'colors' => $type === 'part'
                    ? Color::query()
                        ->where('bricklink_color_id', '!=', '0')
                        ->whereHas('products', fn ($p) => $p->where('stock', '>', 0)->where('productable_type', (new Part)->getMorphClass()))
                        ->orderBy('name')
                        ->limit(80)
                        ->get(['id', 'name', 'hex'])
                    : [],
            ],
            'active' => [
                'category_id' => $categoryId,
                'color_id' => $colorId,
                'q' => null,
            ],
        ]);
    }
}
