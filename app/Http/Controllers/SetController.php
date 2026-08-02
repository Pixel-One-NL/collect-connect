<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Set\SetResource;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use App\Models\Set;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class SetController extends Controller
{
    public function show(Request $request, Set $set): Response
    {
        $set->load('media');

        $categoryId = $request->integer('category_id') ?: null;
        $colorId = $request->integer('color_id') ?: null;

        $partQuantities = DB::table('inventory_parts')
            ->join('inventories', 'inventories.id', '=', 'inventory_parts.inventory_id')
            ->where('inventories.set_id', $set->id)
            ->groupBy('inventory_parts.part_id')
            ->select('inventory_parts.part_id', DB::raw('SUM(inventory_parts.quantity) as quantity'))
            ->pluck('quantity', 'part_id');

        $minifigQuantities = DB::table('inventory_minifigs')
            ->join('inventories', 'inventories.id', '=', 'inventory_minifigs.inventory_id')
            ->where('inventories.set_id', $set->id)
            ->groupBy('inventory_minifigs.minifig_id')
            ->select('inventory_minifigs.minifig_id', DB::raw('SUM(inventory_minifigs.quantity) as quantity'))
            ->pluck('quantity', 'minifig_id');

        $partQuery = Product::query()
            ->where('productable_type', (new Part)->getMorphClass())
            ->whereIn('productable_id', $partQuantities->keys())
            ->with(\App\Domain\Product\Queries\ProductListingQuery::forType('part'))
            ->when($colorId, fn ($q) => $q->where('color_id', $colorId))
            ->when($categoryId, function ($q) use ($categoryId): void {
                $q->whereHasMorph('productable', [Part::class], fn ($p) => $p->where('part_category_id', $categoryId));
            });

        $partProducts = $partQuery->get()
            ->sortByDesc('stock')
            ->unique('productable_id')
            ->values();

        $minifigProducts = Product::query()
            ->where('productable_type', (new Minifig)->getMorphClass())
            ->whereIn('productable_id', $minifigQuantities->keys())
            ->with(['productable.media', 'color'])
            ->get()
            ->sortByDesc('stock')
            ->values();

        $mapPart = fn (Product $product): array => [
            'id' => $product->id,
            'title' => $product->productable->name,
            'lego_number' => $product->productable->bricklink_id,
            'image' => $this->getPartImage($product->productable, (int) $product->color_id),
            'stock' => $product->stock > 100 ? 101 : $product->stock,
            'price' => $product->price,
            'url' => route('product.show', $product->id),
            'color' => $product->color ? ['name' => $product->color->name, 'hex' => $product->color->hex] : null,
            'quantity_in_set' => (int) ($partQuantities[$product->productable_id] ?? 1),
            'type' => 'part',
        ];

        $mapMinifig = fn (Product $product): array => [
            'id' => $product->id,
            'title' => $product->productable->name,
            'lego_number' => $product->productable->bricklink_id,
            'image' => $this->getMinifigImage($product->productable),
            'stock' => $product->stock > 100 ? 101 : $product->stock,
            'price' => $product->price,
            'url' => route('product.show', $product->id),
            'color' => null,
            'quantity_in_set' => (int) ($minifigQuantities[$product->productable_id] ?? 1),
            'type' => 'minifig',
        ];

        $inStock = $partProducts->filter(fn (Product $p): bool => $p->stock > 0)->map($mapPart)
            ->merge($minifigProducts->filter(fn (Product $p): bool => $p->stock > 0)->map($mapMinifig))
            ->values();

        $outOfStock = $partProducts->filter(fn (Product $p): bool => $p->stock === 0)->map($mapPart)
            ->merge($minifigProducts->filter(fn (Product $p): bool => $p->stock === 0)->map($mapMinifig))
            ->values();

        $categories = PartCategory::query()
            ->whereIn('id', Part::query()->whereIn('id', $partQuantities->keys())->pluck('part_category_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return inertia('sets/show', [
            'set' => SetResource::make($set),
            'in_stock_parts' => $inStock,
            'out_of_stock_parts' => $outOfStock,
            'filters' => [
                'categories' => $categories,
            ],
            'active' => [
                'category_id' => $categoryId,
                'color_id' => $colorId,
            ],
        ]);
    }

    /**
     * Set BOM images are media-library only (no Bricqer CDN hotlinking).
     */
    private function getPartImage(Part $part, int $colorId): ?string
    {
        $partColor = $part->partColors->firstWhere('color_id', $colorId);

        return MediaUrl::fromMedia(
            $partColor?->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION),
            [PartColor::LARGE_CONVERSION, PartColor::MEDIUM_CONVERSION, PartColor::THUMB_CONVERSION],
        );
    }

    private function getMinifigImage(Minifig $minifig): ?string
    {
        return MediaUrl::fromMedia(
            $minifig->getFirstMedia(Minifig::BRICQER_IMAGE_COLLECTION),
            [Minifig::LARGE_CONVERSION, Minifig::MEDIUM_CONVERSION, Minifig::THUMB_CONVERSION],
        );
    }
}
