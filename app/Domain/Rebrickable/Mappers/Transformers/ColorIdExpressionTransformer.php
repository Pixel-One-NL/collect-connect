<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Transformers;

use App\Domain\Rebrickable\Contracts\RebrickableFieldTransformer;
use Illuminate\Database\Query\Expression;

class ColorIdExpressionTransformer implements RebrickableFieldTransformer
{
    /**
     * @return Expression<non-falsy-string>
     */
    public function transform(mixed $value): Expression
    {
        return new Expression("(SELECT id FROM colors WHERE rebrickable_id = {$value})");
    }
}
