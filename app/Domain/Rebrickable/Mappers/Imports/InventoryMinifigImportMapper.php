<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Imports;

use App\Domain\Rebrickable\Mappers\BaseImportMapper;

class InventoryMinifigImportMapper extends BaseImportMapper
{
    protected string|array $uniqueKey = ['inventory_id', 'minifig_id'];

    protected array $mapping = [
        'inventory_id' => 'inventory_id',
        'fig_num' => 'minifig_id',
        'quantity' => 'quantity',
    ];
}
