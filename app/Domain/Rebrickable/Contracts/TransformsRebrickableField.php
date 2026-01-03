<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Contracts;

interface TransformsRebrickableField
{
    public function transform(mixed $value): mixed;
}
