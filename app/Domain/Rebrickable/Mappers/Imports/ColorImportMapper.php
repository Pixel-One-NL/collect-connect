<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Imports;

use App\Domain\Rebrickable\Mappers\BaseImportMapper;
use App\Domain\Rebrickable\Mappers\Transformers\BooleanTransformer;

class ColorImportMapper extends BaseImportMapper
{
    protected array $mapping = [
        'id' => 'rebrickable_id',
        'name' => 'name',
        'rgb' => 'hex',
        'is_trans' => 'is_transparent',
    ];

    protected array $transformers = [
        'is_trans' => BooleanTransformer::class,
    ];
}
