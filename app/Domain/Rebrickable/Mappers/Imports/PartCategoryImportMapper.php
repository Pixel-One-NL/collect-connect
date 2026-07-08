<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Imports;

use App\Domain\Rebrickable\Mappers\BaseImportMapper;

class PartCategoryImportMapper extends BaseImportMapper
{
    protected array $mapping = [
        'id' => 'rebrickable_id',
        'name' => 'name',
    ];
}
