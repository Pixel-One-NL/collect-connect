<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services\Imports;

use App\Domain\Rebrickable\Mappers\Imports\InventoryMinifigImportMapper;
use App\Domain\Rebrickable\Services\BaseImportService;
use App\Models\Pivots\InventoryMinifig;
use Illuminate\Support\Facades\DB;

class InventoryMinifigImportService extends BaseImportService
{
    public function getUrl(): string
    {
        return 'https://cdn.rebrickable.com/media/downloads/inventory_minifigs.csv.zip';
    }

    /**
     * {@inheritDoc}
     */
    public function getModel(): string
    {
        return InventoryMinifig::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapper(): string
    {
        return InventoryMinifigImportMapper::class;
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
            $selects[] = 'SELECT ? AS inventory_id, ? AS minifig_id, ? AS quantity';
            foreach ($mappings as $mapping) {
                $bindings[] = data_get($row, $mapping);
            }
        }

        $selectsString = implode(' UNION ALL ', $selects);

        DB::statement("
            INSERT INTO inventory_minifigs (inventory_id, minifig_id, quantity)
            SELECT inventories.id, minifigs.id, source.quantity
            FROM ({$selectsString}) AS source
            INNER JOIN inventories ON inventories.rebrickable_id = source.inventory_id
            INNER JOIN minifigs ON minifigs.id = source.minifig_id
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
        ", $bindings);
    }
}
