<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Jobs;

use App\Integrations\Bricqer\DataTransferObjects\UnconsolidatedInventory\InventoryItem;
use App\Integrations\Bricqer\Facades\Bricqer;
use App\Models\Color;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SyncBricqerInventoryJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var int<1, max>
     */
    protected int $chunkSize = 1000;

    /**
     * @var Collection<int, string>
     */
    protected Collection $colorIdMap;

    public function __construct() {}

    public function handle(): void
    {
        $this->colorIdMap = $this->retrieveColorIdMap();

        $consolidated = $this->consolidate();

        foreach (array_chunk($consolidated, $this->chunkSize) as $chunk) {
            $this->upsertProducts($chunk);
        }
    }

    /**
     * @return Collection<int, string>
     */
    protected function retrieveColorIdMap(): Collection
    {
        return Color::query()
            ->whereNotNull('bricklink_color_id')
            ->pluck('id', 'bricklink_color_id');
    }

    /**
     * Stream the unconsolidated inventory and reduce the many entries per
     * part+color into a single stock (summed) and price (highest). A part in a
     * specific color is a distinct product, so entries are keyed by item id and
     * color id. Only new-condition part rows (item type "P") are considered.
     *
     * @return list<array{item_id: string, color_id: string, stock: int, price: float}>
     */
    protected function consolidate(): array
    {
        $consolidated = [];

        /** @var InventoryItem $item */
        foreach (Bricqer::lego()->report()->unconsolidatedInventory()->get() as $item) {
            if ($item->itemType !== 'P') {
                continue;
            }

            if ($item->condition !== 'N') { // Only import new parts
                continue;
            }

            $key = "{$item->itemId}_{$item->colorId}";

            if (! $item->definitionId) {
                dump('test');
            }

            if (! isset($consolidated[$key])) {
                $consolidated[$key] = [
                    'item_id' => $item->itemId,
                    'color_id' => $item->colorId,
                    'stock' => $item->remainingQuantity,
                    'price' => (int) round($item->price * 100),
                    'definition_id' => $item->definitionId,
                ];

                continue;
            }

            $consolidated[$key]['stock'] += $item->remainingQuantity;
            $consolidated[$key]['price'] = max($consolidated[$key]['price'], $item->price);
        }

        return array_values($consolidated);
    }

    /**
     * @param  list<array{item_id: string, color_id: string, stock: int, price: float}>  $chunk
     */
    protected function upsertProducts(array $chunk): void
    {
        DB::transaction(function () use ($chunk): void {
            $bricqerIds = array_unique(data_get($chunk, '*.item_id'));

            $partIds = Part::query()
                ->whereIn('bricklink_id', $bricqerIds)
                ->pluck('id', 'bricklink_id')
                ->mapWithKeys(fn (int $id, string $bricklinkId): array => [strtolower($bricklinkId) => $id]);

            foreach ($chunk as $part) {
                // Check if the part and color exist, if not skip this entry
                $colorId = $this->colorIdMap->get(data_get($part, 'color_id'));
                $partId = $partIds->get(strtolower(data_get($part, 'item_id')));

                if (! $colorId || ! $partId) {
                    continue;
                }

                Product::updateOrCreate([
                    'productable_type' => (new Part)->getMorphClass(),
                    'productable_id' => $partId,
                    'color_id' => $colorId,
                ], [
                    'stock' => data_get($part, 'stock'),
                    'price' => data_get($part, 'price'),
                ]);

                PartColor::updateOrCreate([
                    'part_id' => $partId,
                    'color_id' => $colorId,
                    'bricqer_definition_id' => data_get($part, 'definition_id'),
                ]);
            }
        });
    }
}
