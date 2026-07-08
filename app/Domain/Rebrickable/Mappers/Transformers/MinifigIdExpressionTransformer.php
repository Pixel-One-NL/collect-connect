<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Transformers;

use App\Domain\Rebrickable\Contracts\RebrickableFieldTransformer;
use Illuminate\Database\Query\Expression;

class MinifigIdExpressionTransformer implements RebrickableFieldTransformer
{
    /**
     * @return Expression<non-falsy-string>
     */
    public function transform(mixed $value): Expression
    {
        return new Expression("(SELECT id FROM minifigs WHERE rebrickable_id = '{$value}')");
    }
}
