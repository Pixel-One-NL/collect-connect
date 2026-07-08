<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services\Imports;

use App\Domain\Rebrickable\Mappers\Imports\InventorySetImportMapper;
use App\Domain\Rebrickable\Services\BaseImportService;
use App\Models\Pivots\InventorySet;
use Illuminate\Support\Facades\DB;

class InventorySetImportService extends BaseImportService
{
    public function getUrl(): string
    {
        return 'https://cdn.rebrickable.com/media/downloads/inventory_sets.csv.zip';
    }

    /**
     * {@inheritDoc}
     */
    public function getModel(): string
    {
        return InventorySet::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapper(): string
    {
        return InventorySetImportMapper::class;
    }

    protected function upsertRows(array $rows, array|string $uniqueKey): void
    {
        if (empty($rows)) {
            return;
        }

        $selects = [];
        $bindings = [];

        foreach ($rows as $row) {
            $selects[] = 'SELECT ? AS inventory_id, ? AS set_num, ? AS quantity';
            $bindings[] = $row['inventory_id'];
            $bindings[] = $row['set_num'];
            $bindings[] = $row['quantity'];
        }

        $selectsString = implode(' UNION ALL ', $selects);

        DB::statement("
            INSERT INTO inventory_sets (inventory_id, set_id, quantity)
            SELECT inventories.id, sets.id, source.quantity
            FROM ({$selectsString}) AS source
            INNER JOIN inventories ON inventories.rebrickable_id = source.inventory_id
            INNER JOIN sets ON sets.set_num = source.set_num
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
        ", $bindings);
    }
}
