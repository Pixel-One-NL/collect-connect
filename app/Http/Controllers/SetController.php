<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Set\SetResource;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use App\Models\Set;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class SetController extends Controller
{
    public function show(Set $set): Response
    {
        $set->load('media');

        $partQuantities = DB::table('inventory_parts')
            ->join('inventories', 'inventories.id', '=', 'inventory_parts.inventory_id')
            ->where('inventories.set_id', $set->id)
            ->groupBy('inventory_parts.part_id')
            ->select('inventory_parts.part_id', DB::raw('SUM(inventory_parts.quantity) as quantity'))
            ->pluck('quantity', 'part_id');

        $allPartIds = $partQuantities->keys();

        $products = Product::whereIn('productable_id', $allPartIds)
            ->where('productable_type', Part::class)
            ->with(['productable.partColors.media', 'color'])
            ->get()
            ->sortByDesc('stock')
            ->unique('productable_id')
            ->values();

        $partIdsWithProducts = $products->pluck('productable_id')->unique();

        $partsWithoutProducts = Part::whereIn('id', $allPartIds->diff($partIdsWithProducts)->values())
            ->get();

        $productItems = $products->map(fn (Product $product): array => [
            'id' => $product->id,
            'title' => $product->productable->name,
            'lego_number' => $product->productable->bricklink_id,
            'image' => $this->getPartImage($product->productable, $product->color_id),
            'stock' => $product->stock > 100 ? 101 : $product->stock,
            'price' => $product->price,
            'url' => route('product.show', $product->id),
            'color' => $product->color ? ['name' => $product->color->name, 'hex' => $product->color->hex] : null,
            'quantity_in_set' => (int) ($partQuantities[$product->productable_id] ?? 1),
        ]);

        $unavailableItems = $partsWithoutProducts->map(fn (Part $part): array => [
            'id' => null,
            'title' => $part->name,
            'lego_number' => $part->bricklink_id,
            'image' => null,
            'stock' => 0,
            'price' => null,
            'url' => null,
            'color' => null,
            'quantity_in_set' => (int) ($partQuantities[$part->id] ?? 1),
        ]);

        return inertia('sets/show', [
            'set' => SetResource::make($set),
            'in_stock_parts' => $productItems->filter(fn (array $p): bool => $p['stock'] > 0)->values(),
            'out_of_stock_parts' => $productItems
                ->filter(fn (array $p): bool => $p['stock'] === 0)
                ->merge($unavailableItems)
                ->values(),
        ]);
    }

    private function getPartImage(Part $part, int $colorId): ?string
    {
        $partColor = $part->partColors->firstWhere('color_id', $colorId);

        return $partColor
            ?->getFirstMedia(PartColor::BRICQER_IMAGE_COLLECTION)
            ?->getAvailableUrl([PartColor::LARGE_CONVERSION]);
    }
}
