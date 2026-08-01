<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Services;

use App\Integrations\Bricqer\DataTransferObjects\Definition\Definition;
use App\Models\Minifig;
use App\Models\Part;
use Illuminate\Support\Facades\DB;

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

        $updated = 0;
        $keys = array_map(strval(...), array_keys($attributes));
        $table = (new $model)->getTable();

        foreach (array_chunk($keys, 500) as $keyChunk) {
            $entities = $model::query()
                ->whereNotNull('bricklink_id')
                ->where(function ($query) use ($keyChunk): void {
                    foreach ($keyChunk as $key) {
                        $query->orWhereRaw('LOWER(bricklink_id) = ?', [(string) $key]);
                    }
                })
                ->get(['id', 'bricklink_id', 'weight_grams', 'bricqer_definition_id']);

            foreach ($entities as $entity) {
                $data = $attributes[strtolower((string) $entity->bricklink_id)] ?? null;

                if ($data === null) {
                    continue;
                }

                $payload = [];

                if ($data['weight'] !== null) {
                    $currentWeight = $entity->weight_grams;
                    if ($currentWeight === null || abs((float) $currentWeight - $data['weight']) >= 0.00001) {
                        $payload['weight_grams'] = $data['weight'];
                    }
                }

                if ($entity->bricqer_definition_id !== $data['definition_id']) {
                    $payload['bricqer_definition_id'] = $data['definition_id'];
                }

                if ($payload === []) {
                    continue;
                }

                $updated += DB::table($table)
                    ->where('id', $entity->id)
                    ->update($payload);
            }
        }

        return $updated;
    }
}
