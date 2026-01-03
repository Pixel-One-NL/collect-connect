<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Contracts;

interface MapsRebrickableEntity
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function map(array $row): array;
}
