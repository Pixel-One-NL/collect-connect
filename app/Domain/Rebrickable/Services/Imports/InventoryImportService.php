<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services\Imports;

use App\Domain\Rebrickable\Mappers\Imports\InventoryImportMapper;
use App\Domain\Rebrickable\Services\BaseImportService;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class InventoryImportService extends BaseImportService
{
    public function getUrl(): string
    {
        return 'https://cdn.rebrickable.com/media/downloads/inventories.csv.zip';
    }

    /**
     * {@inheritDoc}
     */
    public function getModel(): string
    {
        return Inventory::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapper(): string
    {
        return InventoryImportMapper::class;
    }

    protected function upsertRows(array $rows, array|string $uniqueKey): void
    {
        if (empty($rows)) {
            return;
        }

        $selects = [];
        $bindings = [];

        foreach ($rows as $row) {
            $selects[] = 'SELECT ? AS rebrickable_id, ? AS set_num';
            $bindings[] = $row['rebrickable_id'];
            $bindings[] = $row['set_num'];
        }

        $selectsString = implode(' UNION ALL ', $selects);

        DB::statement("
            INSERT INTO inventories (rebrickable_id, set_id)
            SELECT source.rebrickable_id, sets.id
            FROM ({$selectsString}) AS source
            LEFT JOIN sets ON sets.set_num = source.set_num
            ON DUPLICATE KEY UPDATE set_id = VALUES(set_id)
        ", $bindings);
    }
}
