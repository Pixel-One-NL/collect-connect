<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Imports;

use App\Domain\Rebrickable\Mappers\BaseImportMapper;
use App\Domain\Rebrickable\Mappers\Transformers\PartCategoryIdExpressionTransformer;

class PartImportMapper extends BaseImportMapper
{
    protected array $mapping = [
        'part_num' => 'rebrickable_id',
        'name' => 'name',
        'part_cat_id' => 'part_category_id',
    ];

    protected array $transformers = [
        'part_cat_id' => PartCategoryIdExpressionTransformer::class,
    ];
}
