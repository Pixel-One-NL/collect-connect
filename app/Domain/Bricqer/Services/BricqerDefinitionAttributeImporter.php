<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Services;

use App\Integrations\Bricqer\DataTransferObjects\Definition\Definition;
use App\Models\Minifig;
use App\Models\Part;
use App\Support\BulkCaseUpdate;

/**
 * Aggregates weight + representative definition id from Bricqer definitions
 * and applies them onto local parts/minifigs by BrickLink id.
 */
class BricqerDefinitionAttributeImporter
{
    /**
     * @var array<string, array{weight: float|null, definition_id: string}>
     */
    protected array $parts = [];

    /**
     * @var array<string, array{weight: float|null, definition_id: string}>
     */
    protected array $minifigs = [];

    public int $skipped = 0;

    public function ingest(Definition $definition): void
    {
        if (! in_array($definition->legoType, ['P', 'M'], true)) {
            $this->skipped++;

            return;
        }

        $key = strtolower($definition->legoId);
        $definitionId = (string) $definition->id;
        $weight = ($definition->weight !== null && $definition->weight > 0)
            ? (float) $definition->weight
            : null;

        if ($definition->legoType === 'P') {
            $this->parts[$key] = $this->merge($this->parts[$key] ?? null, $weight, $definitionId);

            return;
        }

        $this->minifigs[$key] = $this->merge($this->minifigs[$key] ?? null, $weight, $definitionId);
    }

    /**
     * @return array{parts_updated: int, minifigs_updated: int, skipped: int}
     */
    public function flush(): array
    {
        return [
            'parts_updated' => $this->apply(Part::class, $this->parts),
            'minifigs_updated' => $this->apply(Minifig::class, $this->minifigs),
            'skipped' => $this->skipped,
        ];
    }

    /**
     * @param  array{weight: float|null, definition_id: string}|null  $current
     * @return array{weight: float|null, definition_id: string}
     */
    protected function merge(?array $current, ?float $weight, string $definitionId): array
    {
        if ($current === null) {
            return [
                'weight' => $weight,
                'definition_id' => $definitionId,
            ];
        }

        if ($weight !== null) {
            $current['weight'] = max($current['weight'] ?? 0.0, $weight);
        }

        if ((int) $definitionId > (int) $current['definition_id']) {
            $current['definition_id'] = $definitionId;
        }

        return $current;
    }

    /**
     * @param  class-string<Part|Minifig>  $model
     * @param  array<string, array{weight: float|null, definition_id: string}>  $attributes
     */
    protected function apply(string $model, array $attributes): int
    {
        if ($attributes === []) {
            return 0;
        }

        $keys = array_map(strval(...), array_keys($attributes));
        $table = (new $model)->getTable();

        /** @var array<int, true> $updatedIds Set of entity ids touched, so an entity matched by two keys counts once. */
        $updatedIds = [];

        foreach (array_chunk($keys, 500) as $keyChunk) {
            $placeholders = implode(',', array_fill(0, count($keyChunk), '?'));

            $entities = $model::query()
                ->whereNotNull('bricklink_id')
                ->whereRaw("LOWER(bricklink_id) IN ({$placeholders})", $keyChunk)
                ->get(['id', 'bricklink_id', 'weight_grams', 'bricqer_definition_id']);

            $weightUpdates = [];
            $definitionUpdates = [];

            foreach ($entities as $entity) {
                $data = $attributes[strtolower((string) $entity->bricklink_id)] ?? null;

                if ($data === null) {
                    continue;
                }

                $currentWeight = $entity->weight_grams;

                if ($data['weight'] !== null && ($currentWeight === null || abs((float) $currentWeight - $data['weight']) >= 0.00001)) {
                    $weightUpdates[$entity->id] = $data['weight'];
                    $updatedIds[$entity->id] = true;
                }

                if ($entity->bricqer_definition_id !== $data['definition_id']) {
                    $definitionUpdates[$entity->id] = $data['definition_id'];
                    $updatedIds[$entity->id] = true;
                }
            }

            BulkCaseUpdate::apply($table, 'id', 'weight_grams', $weightUpdates);
            BulkCaseUpdate::apply($table, 'id', 'bricqer_definition_id', $definitionUpdates);
        }

        return count($updatedIds);
    }
}
