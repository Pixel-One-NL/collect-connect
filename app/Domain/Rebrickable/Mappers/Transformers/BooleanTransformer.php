<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers\Transformers;

use App\Domain\Rebrickable\Contracts\RebrickableFieldTransformer;

class BooleanTransformer implements RebrickableFieldTransformer
{
    public function transform(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return str($value)->is('True');
    }
}
