<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Jobs;

use App\Integrations\Bricqer\DataTransferObjects\UnconsolidatedInventory\InventoryItem;
use App\Integrations\Bricqer\Facades\Bricqer;
use App\Models\Color;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Pivots\PartColor;
use App\Models\Product;
use App\Models\SyncRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SyncBricqerInventoryJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var int<1, max>
     */
    protected int $chunkSize = 1000;

    /**
     * @var Collection<string, int>
     */
    protected Collection $colorIdMap;

    /**
     * @var list<int>
     */
    protected array $syncedProductIds = [];

    /**
     * @return array{
     *     found: int,
     *     color_unmatched: int,
     *     item_not_found: int,
     *     zeroed: int,
     *     minifig_definitions_updated: int,
     *     part_definitions_updated: int
     * }
     */
    public function handle(): array
    {
        $run = SyncRun::query()->create([
            'source' => 'bricqer_inventory',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $this->colorIdMap = $this->retrieveColorIdMap();
            $this->syncedProductIds = [];

            $previouslyOutOfStock = Product::query()
                ->where('stock', '<=', 0)
                ->pluck('id')
                ->all();

            $stats = [
                'found' => 0,
                'color_unmatched' => 0,
                'item_not_found' => 0,
                'zeroed' => 0,
                'minifig_definitions_updated' => 0,
                'part_definitions_updated' => 0,
            ];

            $consolidated = $this->consolidate();

            // Never wipe the shop if the feed is empty/misconfigured.
            if ($consolidated === []) {
                throw new RuntimeException(
                    'Bricqer unconsolidated inventory returned no new-condition P/M rows; refusing to zero stock.',
                );
            }

            // Aggregate definition ids across the full feed first so chunk order
            // cannot regress a higher definition id written by an earlier chunk.
            $stats['minifig_definitions_updated'] = $this->applyMinifigDefinitionIdsFromInventory(
                $consolidated,
                $this->mapBricklinkIds(
                    array_column(array_filter($consolidated, fn (array $r): bool => $r['item_type'] === 'M'), 'item_id'),
                    Minifig::class,
                ),
            );
            $stats['part_definitions_updated'] = $this->applyPartDefinitionIdsFromInventory(
                $consolidated,
                $this->mapBricklinkIds(
                    array_column(array_filter($consolidated, fn (array $r): bool => $r['item_type'] === 'P'), 'item_id'),
                    Part::class,
                ),
            );

            foreach (array_chunk($consolidated, $this->chunkSize) as $chunk) {
                $chunkStats = $this->upsertProducts($chunk);
                $stats['found'] += $chunkStats['found'];
                $stats['color_unmatched'] += $chunkStats['color_unmatched'];
                $stats['item_not_found'] += $chunkStats['item_not_found'];
            }

            $stats['zeroed'] = $this->zeroStockForMissingProducts();

            $restockedIds = Product::query()
                ->whereIn('id', $previouslyOutOfStock)
                ->where('stock', '>', 0)
                ->pluck('id')
                ->all();

            if ($restockedIds !== []) {
                DispatchStockNotificationsJob::dispatch($restockedIds);
            }

            $run->update([
                'status' => 'succeeded',
                'stats' => $stats,
                'finished_at' => now(),
            ]);

            return $stats;
        } catch (Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * @return Collection<string, int>
     */
    protected function retrieveColorIdMap(): Collection
    {
        return Color::query()
            ->whereNotNull('bricklink_color_id')
            ->pluck('id', 'bricklink_color_id')
            ->mapWithKeys(fn (int $id, string|int $bricklinkColorId): array => [(string) $bricklinkColorId => $id]);
    }

    /**
     * @return list<array{item_type: string, item_id: string, color_id: string, stock: int, price: int, definition_id: string}>
     */
    protected function consolidate(): array
    {
        $consolidated = [];

        /** @var InventoryItem $item */
        foreach (Bricqer::lego()->report()->unconsolidatedInventory()->get() as $item) {
            if (! in_array($item->itemType, ['P', 'M'], true)) {
                continue;
            }

            if ($item->condition !== 'N') {
                continue;
            }

            $colorId = $item->colorId !== '' ? $item->colorId : '0';
            $key = "{$item->itemType}_{$item->itemId}_{$colorId}";
            $priceInCents = (int) round($item->price * 100);
            $definitionId = trim($item->definitionId);

            if (! isset($consolidated[$key])) {
                $consolidated[$key] = [
                    'item_type' => $item->itemType,
                    'item_id' => $item->itemId,
                    'color_id' => $colorId,
                    'stock' => $item->remainingQuantity,
                    'price' => $priceInCents,
                    'definition_id' => $definitionId,
                ];

                continue;
            }

            $consolidated[$key]['stock'] += $item->remainingQuantity;
            $consolidated[$key]['price'] = max($consolidated[$key]['price'], $priceInCents);
            $consolidated[$key]['definition_id'] = $this->preferDefinitionId(
                $consolidated[$key]['definition_id'],
                $definitionId,
            );
        }

        return array_values($consolidated);
    }

    /**
     * Keep the best definition id when multiple lots consolidate: prefer any
     * meaningful non-empty value (treat "0" as unset), and when both are set
     * prefer the higher numeric id (newer Bricqer definition). Never regress
     * a real id to "0".
     */
    protected function preferDefinitionId(string $current, string $candidate): string
    {
        $current = $this->normalizeDefinitionId($current);
        $candidate = $this->normalizeDefinitionId($candidate);

        if ($candidate === '') {
            return $current;
        }

        if ($current === '') {
            return $candidate;
        }

        if (ctype_digit($current) && ctype_digit($candidate)) {
            return (int) $candidate > (int) $current ? $candidate : $current;
        }

        return $candidate;
    }

    protected function normalizeDefinitionId(string $definitionId): string
    {
        $definitionId = trim($definitionId);

        return $definitionId === '0' ? '' : $definitionId;
    }

    /**
     * @param  list<array{item_type: string, item_id: string, color_id: string, stock: int, price: int, definition_id: string}>  $chunk
     * @return array{found: int, color_unmatched: int, item_not_found: int}
     */
    protected function upsertProducts(array $chunk): array
    {
        $stats = [
            'found' => 0,
            'color_unmatched' => 0,
            'item_not_found' => 0,
        ];

        DB::transaction(function () use ($chunk, &$stats): void {
            $partIds = $this->mapBricklinkIds(
                array_column(array_filter($chunk, fn (array $r): bool => $r['item_type'] === 'P'), 'item_id'),
                Part::class,
            );

            $minifigIds = $this->mapBricklinkIds(
                array_column(array_filter($chunk, fn (array $r): bool => $r['item_type'] === 'M'), 'item_id'),
                Minifig::class,
            );

            $partMorph = (new Part)->getMorphClass();
            $minifigMorph = (new Minifig)->getMorphClass();

            foreach ($chunk as $row) {
                $colorId = $this->colorIdMap->get((string) $row['color_id']);

                if (! $colorId) {
                    $stats['color_unmatched']++;

                    continue;
                }

                if ($row['item_type'] === 'P') {
                    $entityId = $partIds->get(strtolower($row['item_id']));
                    $morph = $partMorph;
                } else {
                    $entityId = $minifigIds->get(strtolower($row['item_id']));
                    $morph = $minifigMorph;
                }

                if (! $entityId) {
                    $stats['item_not_found']++;

                    continue;
                }

                $product = Product::updateOrCreate([
                    'productable_type' => $morph,
                    'productable_id' => $entityId,
                    'color_id' => $colorId,
                ], [
                    'stock' => $row['stock'],
                    'price' => $row['price'],
                ]);

                $this->syncedProductIds[] = $product->id;
                $stats['found']++;

                if ($row['item_type'] === 'P') {
                    $partColorAttributes = [];

                    if ($row['definition_id'] !== '') {
                        $existing = PartColor::query()
                            ->where('part_id', $entityId)
                            ->where('color_id', $colorId)
                            ->value('bricqer_definition_id');

                        $partColorAttributes['bricqer_definition_id'] = $this->preferDefinitionId(
                            (string) ($existing ?? ''),
                            $row['definition_id'],
                        );
                    }

                    // Ensure pivot exists even without a definition id (stock/catalog path).
                    if ($partColorAttributes === []) {
                        PartColor::query()->firstOrCreate(
                            [
                                'part_id' => $entityId,
                                'color_id' => $colorId,
                            ],
                            [
                                'bricqer_definition_id' => '0',
                            ],
                        );
                    } else {
                        PartColor::updateOrCreate(
                            [
                                'part_id' => $entityId,
                                'color_id' => $colorId,
                            ],
                            $partColorAttributes,
                        );
                    }
                }
            }
        });

        return $stats;
    }

    /**
     * @param  list<array{item_type: string, item_id: string, color_id: string, stock: int, price: int, definition_id: string}>  $rows
     * @param  Collection<string, int>  $minifigIds
     */
    protected function applyMinifigDefinitionIdsFromInventory(array $rows, Collection $minifigIds): int
    {
        /** @var array<string, string> $definitionsByBricklink */
        $definitionsByBricklink = [];

        foreach ($rows as $row) {
            if ($row['item_type'] !== 'M' || $row['definition_id'] === '') {
                continue;
            }

            $key = strtolower($row['item_id']);
            $definitionsByBricklink[$key] = $this->preferDefinitionId(
                $definitionsByBricklink[$key] ?? '',
                $row['definition_id'],
            );
        }

        return $this->bulkUpdateDefinitionIds(Minifig::class, $minifigIds, $definitionsByBricklink);
    }

    /**
     * @param  list<array{item_type: string, item_id: string, color_id: string, stock: int, price: int, definition_id: string}>  $rows
     * @param  Collection<string, int>  $partIds
     */
    protected function applyPartDefinitionIdsFromInventory(array $rows, Collection $partIds): int
    {
        /** @var array<string, string> $definitionsByBricklink */
        $definitionsByBricklink = [];

        foreach ($rows as $row) {
            if ($row['item_type'] !== 'P' || $row['definition_id'] === '') {
                continue;
            }

            $key = strtolower($row['item_id']);
            $definitionsByBricklink[$key] = $this->preferDefinitionId(
                $definitionsByBricklink[$key] ?? '',
                $row['definition_id'],
            );
        }

        return $this->bulkUpdateDefinitionIds(Part::class, $partIds, $definitionsByBricklink);
    }

    /**
     * @param  class-string<Part|Minifig>  $model
     * @param  Collection<string, int>  $entityIdsByBricklink
     * @param  array<string, string>  $definitionsByBricklink
     */
    protected function bulkUpdateDefinitionIds(string $model, Collection $entityIdsByBricklink, array $definitionsByBricklink): int
    {
        if ($definitionsByBricklink === [] || $entityIdsByBricklink->isEmpty()) {
            return 0;
        }

        $candidateIds = [];
        $candidatesById = [];

        foreach ($definitionsByBricklink as $bricklinkId => $definitionId) {
            $entityId = $entityIdsByBricklink->get($bricklinkId);

            if ($entityId === null || $definitionId === '') {
                continue;
            }

            $candidateIds[] = $entityId;
            $candidatesById[$entityId] = $this->preferDefinitionId(
                $candidatesById[$entityId] ?? '',
                $definitionId,
            );
        }

        if ($candidatesById === []) {
            return 0;
        }

        $entities = $model::query()
            ->whereIn('id', array_keys($candidatesById))
            ->get(['id', 'bricqer_definition_id'])
            ->keyBy('id');

        $toUpdate = [];

        foreach ($candidatesById as $entityId => $definitionId) {
            $entity = $entities->get($entityId);

            if ($entity === null) {
                continue;
            }

            $current = (string) ($entity->bricqer_definition_id ?? '');
            $best = $this->preferDefinitionId($current, $definitionId);

            if ($best !== '' && $best !== $current) {
                $toUpdate[$entityId] = $best;
            }
        }

        if ($toUpdate === []) {
            return 0;
        }

        $cases = [];
        $bindings = [];

        foreach ($toUpdate as $entityId => $definitionId) {
            $cases[] = 'WHEN ? THEN ?';
            $bindings[] = $entityId;
            $bindings[] = $definitionId;
        }

        $ids = array_keys($toUpdate);
        $idPlaceholders = implode(',', array_fill(0, count($ids), '?'));
        $caseSql = implode(' ', $cases);
        $table = (new $model)->getTable();

        return DB::update(
            "UPDATE {$table}
             SET bricqer_definition_id = CASE id {$caseSql} END
             WHERE id IN ({$idPlaceholders})",
            [...$bindings, ...$ids],
        );
    }

    /**
     * @param  list<string>  $bricklinkIds
     * @param  class-string<Part|Minifig>  $model
     * @return Collection<string, int>
     */
    protected function mapBricklinkIds(array $bricklinkIds, string $model): Collection
    {
        $bricklinkIds = array_values(array_unique($bricklinkIds));
        $lowerIds = array_map(strtolower(...), $bricklinkIds);

        if ($lowerIds === []) {
            return collect();
        }

        $placeholders = implode(',', array_fill(0, count($lowerIds), '?'));

        return $model::query()
            ->whereNotNull('bricklink_id')
            ->whereRaw("LOWER(bricklink_id) IN ({$placeholders})", $lowerIds)
            ->get(['id', 'bricklink_id'])
            ->mapWithKeys(fn (Part|Minifig $entity): array => [strtolower((string) $entity->bricklink_id) => $entity->id]);
    }

    protected function zeroStockForMissingProducts(): int
    {
        // Require that we actually synced something before zeroing outsiders.
        if ($this->syncedProductIds === []) {
            return 0;
        }

        return Product::query()
            ->where('stock', '>', 0)
            ->whereNotIn('id', array_unique($this->syncedProductIds))
            ->update(['stock' => 0]);
    }
}
