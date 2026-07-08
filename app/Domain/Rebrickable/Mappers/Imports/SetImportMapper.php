<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Imports;

use App\Domain\Rebrickable\Mappers\BaseImportMapper;

class SetImportMapper extends BaseImportMapper
{
    protected array $mapping = [
        'set_num' => 'set_num',
        'name' => 'name',
        'year' => 'year',
        'theme_id' => 'theme_id',
        'num_parts' => 'num_parts',
        'img_url' => 'img_url',
    ];

    protected string|array $uniqueKey = 'set_num';
}
