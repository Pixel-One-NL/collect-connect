<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Contracts;

interface RebrickableFieldTransformer
{
    public function transform(mixed $value): mixed;
}
