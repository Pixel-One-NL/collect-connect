<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Imports;

use App\Domain\Rebrickable\Mappers\BaseImportMapper;

class MinifigImportMapper extends BaseImportMapper
{
    protected array $mapping = [
        'fig_num' => 'rebrickable_id',
        'name' => 'name',
    ];
}
