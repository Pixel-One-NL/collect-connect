<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services\Imports;

use App\Domain\Rebrickable\Mappers\Imports\InventoryPartImportMapper;
use App\Domain\Rebrickable\Services\BaseImportService;
use App\Models\Pivots\InventoryPart;
use Illuminate\Support\Facades\DB;

class InventoryPartImportService extends BaseImportService
{
    public function getUrl(): string
    {
        return 'https://cdn.rebrickable.com/media/downloads/inventory_parts.csv.zip';
    }

    /**
     * {@inheritDoc}
     */
    public function getModel(): string
    {
        return InventoryPart::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapper(): string
    {
        return InventoryPartImportMapper::class;
    }

    protected function upsertRows(array $rows, array|string $uniqueKey): void
    {
        if (empty($rows)) {
            return;
        }

        $mappings = array_values($this->mapper->getMapping());
        $selects = [];
        $bindings = [];

        foreach ($rows as $row) {
            $selects[] = 'SELECT ? AS inventory_id, ? AS part_id, ? AS color_id, ? AS quantity, ? AS is_spare';
            foreach ($mappings as $mapping) {
                $bindings[] = data_get($row, $mapping);
            }
        }

        $selectsString = implode(' UNION ALL ', $selects);

        DB::statement("
            INSERT INTO inventory_parts (inventory_id, part_id, color_id, quantity, is_spare)
            SELECT inventories.id, parts.id, colors.id, source.quantity, source.is_spare
            FROM ({$selectsString}) AS source
            INNER JOIN inventories ON inventories.rebrickable_id = source.inventory_id
            INNER JOIN parts ON parts.id = source.part_id
            INNER JOIN colors ON colors.id = source.color_id
            ON DUPLICATE KEY UPDATE
                quantity = VALUES(quantity),
                is_spare = VALUES(is_spare)
        ", $bindings);
    }
}
